from flask import Flask, jsonify, request
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import normalize
import logging
from flask_cors import CORS
from flask_socketio import SocketIO

# Konfigurasi dasar aplikasi Flask dan SocketIO
app = Flask(__name__)
CORS(app)
socketio = SocketIO(app, cors_allowed_origins="*")

# Pengaturan logging
logging.basicConfig(level=logging.DEBUG)

# Load datasets dengan penanganan error
try:
    pasien_df = pd.read_csv('models/data_pasien.csv')
    diagnosis_df = pd.read_csv('models/data_diagnosis_penyakit_new.csv')
    obat_df = pd.read_csv('models/data_resep_obat_new.csv')
    logging.info("Datasets loaded successfully.")
except Exception as e:
    logging.error(f"Error loading datasets: {e}")
    raise e

# Inisialisasi TF-IDF Vectorizer untuk diagnosis
try:
    tfidf = TfidfVectorizer(stop_words='english', ngram_range=(1, 2))
    tfidf_matrix = tfidf.fit_transform(diagnosis_df['Diagnosis'])
    logging.info("TF-IDF Vectorizer created successfully.")
except Exception as e:
    logging.error("Error creating TF-IDF Vectorizer: %s", e)
    raise e

# Route untuk mendapatkan data pasien
@app.route('/api/pasien', methods=['GET'])
def get_pasien():
    try:
        sorted_pasien_df = pasien_df.sort_values(by='Nama', ascending=True)
        pasien_list = sorted_pasien_df.to_dict(orient='records')
        return jsonify(pasien_list)
    except Exception as e:
        logging.error("Error in get_pasien: %s", e)
        return jsonify({'error': 'An error occurred while fetching pasien data.'}), 500

# Event handler untuk SocketIO
@socketio.on('connect')
def test_connect():
    print("Client connected")

@socketio.on('disconnect')
def test_disconnect():
    print("Client disconnected")

# Route untuk menambah data pasien baru
@app.route('/api/add_pasien', methods=['POST'])
def add_pasien():
    try:
        data = request.get_json()
        new_row = pd.DataFrame([data])
        global pasien_df

        # Menyimpan data baru ke CSV
        new_row.to_csv('models/data_pasien.csv', mode='a', header=False, index=False)

        # Tambahkan data baru ke dataset
        pasien_df = pd.concat([pasien_df, new_row], ignore_index=True)

        # Emit event ke frontend
        socketio.emit('new_pasien', data, broadcast=True)
        return jsonify({'message': 'Patient data successfully added!'}), 200
    except Exception as e:
        logging.error("Error adding pasien: %s", e)
        return jsonify({'error': str(e)}), 500

# Endpoint Content-Based Filtering
@app.route('/api/cbf', methods=['POST'])
def content_filtering():
    try:
        data = request.get_json()
        diagnosis_text = data.get('diagnosis')

        if not diagnosis_text:
            logging.error("Diagnosis input is missing.")
            return jsonify({'error': 'Diagnosis input is required'}), 400

        tfidf_diagnosis = tfidf.transform([diagnosis_text.lower().strip()])
        tfidf_matrix_normalized = normalize(tfidf_matrix)
        cosine_sim = cosine_similarity(tfidf_diagnosis, tfidf_matrix_normalized).flatten()

        threshold = 0.5
        top_indices = [i for i in range(len(cosine_sim)) if cosine_sim[i] > threshold]

        if not top_indices:
            logging.info("No similar diagnosis found with sufficient similarity.")
            return jsonify({'error': 'No similar diagnosis found with sufficient similarity.'}), 400

        top_indices = sorted(top_indices, key=lambda i: cosine_sim[i], reverse=True)
        top_matches = diagnosis_df.iloc[top_indices]['Diagnosis'].tolist()

        return jsonify({
            'diagnosis': diagnosis_text,
            'cosine_similarity': cosine_sim[top_indices[0]],
            'top_matches': top_matches
        }), 200
    except Exception as e:
        logging.error(f"Error in content-filtering: {e}")
        return jsonify({'error': str(e)}), 500

# Endpoint untuk autocomplete diagnosis
@app.route('/api/diagnosis', methods=['GET'])
def get_diagnosis():
    try:
        query = request.args.get('q', '').strip().lower()
        if query:
            filtered_diagnosis = diagnosis_df[diagnosis_df['Diagnosis'].str.contains(query, case=False, na=False)]
            diagnosis_list = filtered_diagnosis['Diagnosis'].tolist()
            if not diagnosis_list:
                return jsonify({'error': 'Diagnosis tidak ditemukan dalam dataset'}), 404
        else:
            diagnosis_list = []
        return jsonify(diagnosis_list)
    except Exception as e:
        logging.error("Error in get_diagnosis: %s", e)
        return jsonify({'error': 'An error occurred while fetching diagnosis data.'}), 500

# Fungsi rekomendasi obat berdasarkan diagnosis, gender, dan umur
def get_recommendations(diagnosis_text, gender, age):
    try:
        diagnosis_text_lower = diagnosis_text.strip().lower()
        if not diagnosis_text_lower:
            return {'error': 'Diagnosis tidak boleh kosong'}

        tfidf_diagnosis = tfidf.transform([diagnosis_text_lower])
        tfidf_matrix_normalized = normalize(tfidf_matrix)
        cosine_sim_diagnosis = cosine_similarity(tfidf_diagnosis, tfidf_matrix_normalized).flatten()

        threshold = 0.5
        top_indices = [i for i in range(len(cosine_sim_diagnosis)) if cosine_sim_diagnosis[i] > threshold]

        if not top_indices:
            return {'error': 'Tidak ada diagnosis yang relevan dengan tingkat kemiripan yang cukup'}

        top_indices = sorted(top_indices, key=lambda i: cosine_sim_diagnosis[i], reverse=True)
        matched_diagnoses = diagnosis_df.iloc[top_indices]

        recommended_obat_list = []
        for _, matched_diagnosis in matched_diagnoses.iterrows():
            filtered_obat = obat_df[
                (obat_df['Diagnosis'].str.lower() == matched_diagnosis['Diagnosis'].lower()) &
                (obat_df['Gender'] == gender) &
                (obat_df['Umur'] == age)
            ]
            if not filtered_obat.empty:
                recommended_obat_list.extend(filtered_obat['Resep Obat'].tolist())

        if not recommended_obat_list:
            return {'error': 'Tidak ada rekomendasi obat yang cocok untuk diagnosis ini berdasarkan umur dan jenis kelamin'}

        return {'Resep Obat': recommended_obat_list}
    except Exception as e:
        logging.error(f"Error in get_recommendations: {e}")
        return {'error': str(e)}

# Endpoint rekomendasi obat
@app.route('/recommend', methods=['POST'])
def recommend():
    try:
        data = request.get_json()
        if not all(k in data for k in ('diagnosis', 'gender', 'age')):
            return jsonify({'error': 'Invalid input. Please provide diagnosis, gender, and age in JSON format.'}), 400

        diagnosis_text = data.get('diagnosis')
        gender = data.get('gender')
        age = int(data.get('age'))

        recommendations = get_recommendations(diagnosis_text, gender, age)
        if 'error' in recommendations:
            return jsonify(recommendations), 400
        return jsonify(recommendations)
    except Exception as e:
        logging.error("Error in recommend endpoint: %s", e)
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    socketio.run(app, host='127.0.0.1', port=5000, debug=True)
