

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
    logging.error(f"Error loading datasets: {e}")
    raise e

# Route to get pasien data
@app.route('/api/pasien', methods=['GET'])
def get_pasien():
    try:
        sorted_pasien_df = pasien_df.sort_values(by='Nama', ascending=True)
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

# Route to add a new patient
@app.route('/api/add_pasien', methods=['POST'])
def add_pasien():
    try:
        data = request.get_json()
        new_row = pd.DataFrame([data])
        global pasien_df

        # Save new data to CSV
        new_row.to_csv('models/data_pasien.csv', mode='a', header=False, index=False)

        # Append the new data to the dataset
        pasien_df = pd.concat([pasien_df, new_row], ignore_index=True)

        # Emit event to update the frontend in real-time
        socketio.emit('new_pasien', data, broadcast=True)  # Emit data pasien baru ke semua klien

        return jsonify({'message': 'Patient data successfully added!'}), 200
    except Exception as e:
        logging.error("Error adding pasien: %s", e)
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    socketio.run(app, debug=True, use_reloader=False, host='0.0.0.0', port=5000)


# Create TF-IDF Vectorizer for diagnosis
try:
    tfidf = TfidfVectorizer(stop_words='english')
    tfidf_matrix = tfidf.fit_transform(diagnosis_df['Diagnosis'])
    logging.info("TF-IDF Vectorizer created successfully.")
except Exception as e:
    logging.error("Error creating TF-IDF Vectorizer: %s", e)
    raise e

# Content-based filtering endpoint
@app.route('/api/cbf', methods=['POST'])
def content_filtering():
    try:
        data = request.get_json()
        logging.info(f"Received data: {data}")

        diagnosis_text = data.get('diagnosis')
        logging.info(f"Diagnosis received: {diagnosis_text}")

        if not diagnosis_text:
            logging.error("No diagnosis input found")
            return jsonify({'error': 'Diagnosis input is required'}), 400

        # Transformasi diagnosis ke TF-IDF
        try:
            tfidf_diagnosis = tfidf.transform([diagnosis_text.lower().strip()])
            logging.info(f"TF-IDF vector generated: {tfidf_diagnosis}")
        except Exception as tfidf_error:
            logging.error(f"Error in TF-IDF transformation: {tfidf_error}")
            return jsonify({'error': 'Error in TF-IDF transformation'}), 500

        # Perhitungan cosine similarity
        try:
            cosine_sim = cosine_similarity(tfidf_diagnosis, tfidf_matrix).flatten()
            logging.info(f"Cosine similarity calculated: {cosine_sim}")
        except Exception as cosine_error:
            logging.error(f"Error calculating cosine similarity: {cosine_error}")
            return jsonify({'error': 'Error calculating cosine similarity'}), 500

        # Temukan diagnosis teratas berdasarkan cosine similarity
        try:
            top_indices = cosine_sim.argsort()[-5:][::-1]
            top_matches = diagnosis_df.iloc[top_indices]['Diagnosis'].tolist()
            logging.info(f"Top matches found: {top_matches}")
        except Exception as match_error:
            logging.error(f"Error finding top matches: {match_error}")
            return jsonify({'error': 'Error finding top matches'}), 500

        # Return hasil dalam JSON
        return jsonify({
            'diagnosis': diagnosis_text,
            'cosine_similarity': cosine_sim[top_indices[0]],
            'top_matches': top_matches
        }), 200
    except Exception as e:
        logging.error(f"Error in content-filtering: {e}")
        return jsonify({'error': str(e)}), 500

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

# Route untuk menyimpan diagnosa dan menghapus pasien dari dataset
@app.route('/api/save_diagnosis', methods=['POST'])
def save_diagnosis():
    try:
        data = request.get_json()
        if 'nama' not in data or 'diagnosis' not in data:
            return jsonify({'error': 'Invalid input. Please provide nama and diagnosis in JSON format.'}), 400

        nama = data['nama']
        diagnosis = data['diagnosis']

        # Cari pasien berdasarkan nama
        global pasien_df
        pasien = pasien_df[pasien_df['Nama'].str.lower() == nama.lower()]

        if pasien.empty:
            return jsonify({'error': 'Pasien tidak ditemukan'}), 404

        # Simpan diagnosis pasien
        new_diagnosis_row = {
            'Nama': nama,
            'Diagnosis': diagnosis
        }
        diagnosis_df.loc[len(diagnosis_df)] = new_diagnosis_row  # Simpan diagnosis baru

        # Hapus data pasien dari dataset pasien_df
        pasien_df = pasien_df[pasien_df['Nama'].str.lower() != nama.lower()]

        # Emit event pasien yang sudah dihapus ke semua klien yang terhubung
        socketio.emit('patient_deleted', {'nama': nama})

        return jsonify({'message': 'Diagnosis disimpan dan data pasien dihapus dari dataset'}), 200
    except Exception as e:
        logging.error("Error in save_diagnosis: %s", e)
        return jsonify({'error': str(e)}), 500

# Jalankan server Flask dengan SocketIO
if __name__ == '__main__':
    socketio.run(app, debug=True, use_reloader=False, host='0.0.0.0', port=5000)
