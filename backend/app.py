from flask import Flask, jsonify, request
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import normalize
import logging
import os
import numpy as np

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


# Route to add a new patient
@app.route('/api/add_pasien', methods=['POST'])
def add_pasien():
    global pasien_df
    try:
        data = request.get_json()
        logging.debug(f"Data received for new patient: {data}")

        # Ambil dan bersihkan input data tanpa mengubah kapitalisasi asli
        nama = data.get('Nama', '').strip()
        gender = data.get('Gender', '').strip()
        umur = str(data.get('Umur', '')).strip()

        # Validasi data input
        if not nama or not gender or not umur:
            logging.error("Invalid input data: Nama, Gender, and Umur are required.")
            return jsonify({'error': 'Nama, Gender, dan Umur diperlukan'}), 400

        # Cek duplikasi di DataFrame yang sudah dimuat, dengan perbandingan case-insensitive
        if any((pasien_df['Nama'].str.lower() == nama.lower()) &
               (pasien_df['Gender'].str.lower() == gender.lower()) &
               (pasien_df['Umur'].astype(str) == umur)):
            logging.info("Data pasien duplikat ditemukan.")
            return jsonify({'error': 'Data pasien sudah ada.'}), 409

        # Menambahkan data baru jika tidak ada duplikasi
        new_row = pd.DataFrame([[nama, gender, umur]], columns=['Nama', 'Gender', 'Umur'])

        # Tentukan file path (gunakan path yang sesuai)
        file_path = 'models/data_pasien.csv'

        new_row.to_csv(file_path, mode='a', header=not os.path.exists(file_path), index=False)

        # Update DataFrame di memori
        pasien_df = pd.concat([pasien_df, new_row], ignore_index=True)
        logging.info("Data pasien berhasil ditambahkan ke CSV dan DataFrame.")

        # Emit event ke frontend jika berhasil
        socketio.emit('new_pasien', data, broadcast=True)
        return jsonify({'message': 'Data pasien berhasil ditambahkan!'}), 200

    except Exception as e:
        logging.error(f"Error adding pasien: {e}")
        return jsonify({'error': str(e)}), 500

# Create TF-IDF Vectorizer for diagnosis
try:
    tfidf = TfidfVectorizer(stop_words='english', ngram_range=(1, 2))  # Menggunakan n-gram untuk mengoptimalkan
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
        diagnosis_text = data.get('diagnosis')

        if not diagnosis_text:
            logging.error("Diagnosis input is missing.")
            return jsonify({'error': 'Diagnosis input is required'}), 400

        # Transformasi diagnosis input ke TF-IDF
        logging.info(f"Transforming diagnosis input to TF-IDF: {diagnosis_text}")
        tfidf_diagnosis = tfidf.transform([diagnosis_text.lower().strip()])
        logging.debug(f"TF-IDF Vector for input diagnosis: {tfidf_diagnosis.toarray()}")

        # Normalisasi sebelum cosine similarity
        logging.info("Normalizing TF-IDF matrix...")
        tfidf_matrix_normalized = normalize(tfidf_matrix)
        cosine_sim = cosine_similarity(tfidf_diagnosis, tfidf_matrix_normalized).flatten()
        logging.debug(f"Cosine Similarity Scores: {cosine_sim}")

        # Terapkan threshold untuk cosine similarity
        threshold = 0.5  # Nilai minimal cosine similarity yang diterima
        top_indices = [i for i in range(len(cosine_sim)) if cosine_sim[i] > threshold]

        if not top_indices:
            logging.info("No similar diagnosis found with sufficient similarity.")
            return jsonify({'error': 'No similar diagnosis found with sufficient similarity.'}), 400

        # Ambil diagnosis teratas berdasarkan cosine similarity dan urutkan dari tertinggi
        top_indices = sorted(top_indices, key=lambda i: cosine_sim[i], reverse=True)
        top_matches = diagnosis_df.iloc[top_indices]['Diagnosis'].tolist()
        logging.info(f"Top matched diagnoses: {top_matches}")

        # Return hasil dalam JSON
        return jsonify({
            'diagnosis': diagnosis_text,
            'cosine_similarity': cosine_sim[top_indices[0]],  # Mengirimkan similarity tertinggi
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

@app.route('/api/delete_diagnosis', methods=['POST'])
def delete_diagnosis():
    try:
        data = request.get_json()
        if 'diagnosis' not in data:
            return jsonify({'error': 'Invalid input. Please provide diagnosis in JSON format.'}), 400

        diagnosis = data['diagnosis'].lower()

        global diagnosis_df
        diagnosis_df = diagnosis_df[diagnosis_df['Diagnosis'].str.lower() != diagnosis]

        # Simpan perubahan ke CSV jika diperlukan
        diagnosis_df.to_csv('models/data_diagnosis_penyakit_new.csv', index=False)

        # Emit event ke klien untuk memperbarui autocomplete
        socketio.emit('diagnosis_deleted', {'diagnosis': diagnosis})

        return jsonify({'message': 'Diagnosis berhasil dihapus'}), 200
    except Exception as e:
        logging.error(f"Error deleting diagnosis: {e}")
        return jsonify({'error': str(e)}), 500


# Function to get recommendations based on diagnosis text
def get_recommendations(diagnosis_text):
    try:
        # Log input diagnosis
        logging.info(f"Received diagnosis input for recommendations: {diagnosis_text}")

        # Convert diagnosis ke lowercase dan buang spasi berlebih
        diagnosis_text_lower = diagnosis_text.strip().lower()

        if not diagnosis_text_lower:
            logging.warning("Empty diagnosis input.")
            return {'error': 'Diagnosis tidak boleh kosong'}

        # Transformasi input diagnosis ke TF-IDF
        logging.info("Transforming input diagnosis to TF-IDF...")
        tfidf_diagnosis = tfidf.transform([diagnosis_text_lower])
        logging.debug(f"TF-IDF Vector for input diagnosis: {tfidf_diagnosis.toarray()}")

        # Normalisasi sebelum cosine similarity
        logging.info("Normalizing TF-IDF matrix for cosine similarity...")
        tfidf_matrix_normalized = normalize(tfidf_matrix)
        cosine_sim_diagnosis = cosine_similarity(tfidf_diagnosis, tfidf_matrix_normalized).flatten()
        logging.debug(f"Cosine Similarity Scores: {cosine_sim_diagnosis}")

        # Terapkan threshold untuk cosine similarity
        threshold = 0.5  # Nilai minimal cosine similarity yang diterima
        top_indices = [i for i in range(len(cosine_sim_diagnosis)) if cosine_sim_diagnosis[i] > threshold]

        if not top_indices:
            logging.info("No relevant diagnosis found with sufficient similarity.")
            return {'error': 'Tidak ada diagnosis yang relevan dengan tingkat kemiripan yang cukup'}

        # Ambil diagnosis teratas berdasarkan cosine similarity dan urutkan dari tertinggi
        top_indices = sorted(top_indices, key=lambda i: cosine_sim_diagnosis[i], reverse=True)
        matched_diagnoses = diagnosis_df.iloc[top_indices]
        logging.info(f"Top matched diagnoses: {matched_diagnoses['Diagnosis'].tolist()}")

        # Koleksi rekomendasi obat berdasarkan diagnosis yang mirip
        recommended_obat_list = []
        for _, matched_diagnosis in matched_diagnoses.iterrows():
            recommended_obat = obat_df[obat_df['Diagnosis'].str.lower() == matched_diagnosis['Diagnosis'].lower()]
            if not recommended_obat.empty:
                recommended_obat_list.extend(recommended_obat['Resep Obat'].tolist())
                logging.debug(f"Recommended medicines for diagnosis '{matched_diagnosis['Diagnosis']}': {recommended_obat['Resep Obat'].tolist()}")

        if not recommended_obat_list:
            logging.info("No medicines found for the diagnosis.")
            return {'error': 'Tidak ada rekomendasi obat untuk diagnosis ini'}

        return {'Resep Obat': recommended_obat_list}  # Tampilkan semua rekomendasi obat tanpa batasan
    except Exception as e:
        logging.error(f"Error in get_recommendations: {e}")
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

    # Event handler for SocketIO
@socketio.on('connect')
def test_connect():
    print("Client connected")

@socketio.on('disconnect')
def test_disconnect():
    print("Client disconnected")

# Function to categorize age
def kategori_umur(umur):
    if umur < 18:
        return "Anak-anak"
    elif 18 <= umur < 30:
        return "Dewasa Muda"
    elif 30 <= umur < 60:
        return "Dewasa"
    else:
        return "Lansia"

# Route to get grouped medication data
@app.route('/api/pengelompokan_resep_obat', methods=['GET'])
def pengelompokan_resep_obat():
    try:
        # Assign random diagnosis from diagnosis_df to each patient in pasien_df for simulation
        np.random.seed(0)  # For reproducibility
        pasien_df['Diagnosis'] = np.random.choice(diagnosis_df['Diagnosis'], size=len(pasien_df))

        # Merge data based on Diagnosis to get matching prescriptions
        merged_df = pd.merge(pasien_df, obat_df, on='Diagnosis', how='inner')
        print("Merged DataFrame:", merged_df.head())  # Debugging output

        # Add Age Category column
        merged_df['Kategori Umur'] = merged_df['Umur'].apply(kategori_umur)

        # Check if merged data is empty
        if merged_df.empty:
            print("No matching data found after merging.")
            return jsonify({'error': 'No matching data found in merged DataFrame.'}), 500

        # Group data by Gender and Age Category, concatenating the prescription lists
        grouped_df = merged_df.groupby(['Gender', 'Kategori Umur'])['Resep Obat'].apply(', '.join).reset_index()
        print("Grouped DataFrame:", grouped_df.head())  # Debugging output

        # Convert to JSON format
        grouped_data = grouped_df.to_dict(orient='records')
        print("JSON Output:", grouped_data)  # Debugging output
        return jsonify(grouped_data), 200

    except Exception as e:
        print(f"Error in pengelompokan_resep_obat: {e}")
        return jsonify({'error': f"Internal Server Error: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True)


# Jalankan server Flask dengan SocketIO
if __name__ == '__main__':
   socketio.run(app, host='127.0.0.1', port=5000, debug=True)
