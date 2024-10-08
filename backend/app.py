from flask import Flask, jsonify, request
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
import logging
logging.basicConfig(level=logging.DEBUG)  # Set logging to DEBUG level
from flask_cors import CORS
from flask_socketio import SocketIO

app = Flask(__name__)
CORS(app)
socketio = SocketIO(app, cors_allowed_origins="*")  # Menginisialisasi SocketIO untuk real-time updates

# Load datasets
try:
    pasien_df = pd.read_csv('models/data_pasien.csv')
    diagnosis_df = pd.read_csv('models/data_diagnosis_penyakit_new.csv')
    obat_df = pd.read_csv('models/data_resep_obat_new.csv')
    logging.info("Datasets loaded successfully.")
except Exception as e:
    logging.error("Error loading datasets: %s", e)
    raise e

# Route to get pasien data
@app.route('/api/pasien', methods=['GET'])
def get_pasien():
    try:
        # Urutkan data pasien berdasarkan kolom 'Nama'
        sorted_pasien_df = pasien_df.sort_values(by='Nama', ascending=True)
        # Convert the sorted pasien data to dictionary format to return as JSON
        pasien_list = sorted_pasien_df.to_dict(orient='records')
        return jsonify(pasien_list)
    except Exception as e:
        logging.error("Error in get_pasien: %s", e)
        return jsonify({'error': 'An error occurred while fetching pasien data.'}), 500

# Event handler for SocketIO
@socketio.on('connect')
def test_connect():
    print("Client connected")

@socketio.on('disconnect')
def test_disconnect():
    print("Client disconnected")

# Route untuk menambah data pasien
@app.route('/api/add_pasien', methods=['POST'])
def add_pasien():
    try:
        data = request.get_json()
        new_row = pd.DataFrame([data])
        global pasien_df
        pasien_df = pd.concat([pasien_df, new_row], ignore_index=True)

        # Emit event pasien baru ke semua klien yang terhubung
        socketio.emit('new_pasien', data)

        return jsonify({'message': 'Data pasien berhasil ditambahkan!'}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Create TF-IDF Vectorizer for diagnosis
try:
    tfidf = TfidfVectorizer(stop_words='english')
    tfidf_matrix = tfidf.fit_transform(diagnosis_df['Diagnosis'])
    logging.info("TF-IDF Vectorizer created successfully.")
except Exception as e:
    logging.error("Error creating TF-IDF Vectorizer: %s", e)
    raise e

@app.route('/api/diagnosis', methods=['GET'])
def get_diagnosis():
    try:
        query = request.args.get('q', '').strip().lower()
        logging.debug(f"Received query: {query}")
        if query:
            # Filter diagnosis yang cocok dengan query
            filtered_diagnosis = diagnosis_df[diagnosis_df['Diagnosis'].str.contains(query, case=False, na=False)]
            diagnosis_list = filtered_diagnosis['Diagnosis'].tolist()
            if not diagnosis_list:
                logging.debug("No matches found for the diagnosis.")
                return jsonify({'error': 'Diagnosis tidak ditemukan dalam dataset'}), 404
            logging.debug(f"Filtered diagnosis: {diagnosis_list}")
        else:
            diagnosis_list = []
            logging.debug("Query kosong.")
        return jsonify(diagnosis_list)
    except Exception as e:
        logging.error("Error in get_diagnosis: %s", e)
        return jsonify({'error': 'An error occurred while fetching diagnosis data.'}), 500



# Function to get recommendations based on diagnosis text
def get_recommendations(diagnosis_text):
    try:
        # Log diagnosis input yang diterima
        logging.debug("Received diagnosis input: %s", diagnosis_text)

        # Convert diagnosis ke lowercase dan buang spasi berlebih
        diagnosis_text_lower = diagnosis_text.strip().lower()

        # Validasi bahwa diagnosis tidak kosong
        if not diagnosis_text_lower:
            logging.warning("Diagnosis input is empty.")
            return {'error': 'Diagnosis tidak boleh kosong'}

        # Logging setelah lowercase
        logging.debug("Processed diagnosis input (lowercased): %s", diagnosis_text_lower)

        # Transformasi input diagnosis ke TF-IDF
        tfidf_diagnosis = tfidf.transform([diagnosis_text_lower])
        logging.debug("TF-IDF vector for input diagnosis: %s", tfidf_diagnosis.toarray())

        # Hitung cosine similarity antara input diagnosis dan seluruh diagnosis di dataset
        cosine_sim_diagnosis = cosine_similarity(tfidf_diagnosis, tfidf_matrix).flatten()
        logging.debug("Cosine similarity scores: %s", cosine_sim_diagnosis)

        # Temukan indeks diagnosis yang paling mirip berdasarkan cosine similarity
        top_indices = cosine_sim_diagnosis.argsort()[-5:][::-1]  # Ambil 5 teratas
        matched_diagnoses = diagnosis_df.iloc[top_indices]

        # Jika tidak ada diagnosis yang cocok
        if matched_diagnoses.empty:
            logging.debug("No matches found for the diagnosis.")
            return {'error': 'Diagnosis tidak ditemukan dalam dataset'}, 404

        logging.debug("Top matched diagnoses based on cosine similarity: %s", matched_diagnoses['Diagnosis'].tolist())

        # Koleksi rekomendasi obat berdasarkan diagnosis yang mirip
        recommended_obat_list = []
        for _, matched_diagnosis in matched_diagnoses.iterrows():
            recommended_obat = obat_df[obat_df['Diagnosis'].str.lower() == matched_diagnosis['Diagnosis'].lower()]
            if not recommended_obat.empty:
                recommended_obat_list.extend(recommended_obat['Resep Obat'].tolist())
                logging.debug("Recommended medicines for diagnosis '%s': %s", matched_diagnosis['Diagnosis'], recommended_obat['Resep Obat'].tolist())

        if not recommended_obat_list:
            logging.debug("No medicines found for the diagnosis: %s", diagnosis_text_lower)
            return {'error': 'Tidak ada rekomendasi obat untuk diagnosis ini'}

        # Kembalikan maksimal 3 rekomendasi obat
        return {'Resep Obat': recommended_obat_list[:3]}
    except Exception as e:
        logging.error("Error in get_recommendations: %s", e)
        return {'error': str(e)}



# Route to get recommendations
@app.route('/recommend', methods=['POST'])
def recommend():
    try:
        data = request.get_json()
        if data is None or 'diagnosis' not in data:
            return jsonify({'error': 'Invalid input. Please provide diagnosis in JSON format.'}), 400

        diagnosis_text = data.get('diagnosis')

        recommendations = get_recommendations(diagnosis_text)
        if 'error' in recommendations:
            return jsonify(recommendations), 400
        return jsonify(recommendations)
    except Exception as e:
        logging.error("Error in recommend endpoint: %s", e)
        return jsonify({'error': str(e)}), 500

# Backend: validasi dan pembersihan sebelum menyimpan
def clean_resep_obat(resep_obat):
    # Hilangkan elemen kosong atau hanya spasi
    resep_obat_list = [item.strip() for item in resep_obat.split(',') if item.strip()]
    return ', '.join(resep_obat_list)  # Gabungkan ulang string tanpa elemen kosong

# Sebelum menyimpan ke database
resep_obat_string = "default_value"
resep_obat = clean_resep_obat(resep_obat_string)



# Jalankan server Flask dengan SocketIO
if __name__ == '__main__':
    socketio.run(app, debug=True, use_reloader=False)
