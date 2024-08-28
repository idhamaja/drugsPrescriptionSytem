from flask import Flask, jsonify, request
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
import logging
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Mengizinkan semua asal (origin) untuk akses API

# Set up logging
logging.basicConfig(level=logging.DEBUG)

# Load datasets
try:
    pasien_df = pd.read_csv('models/data_pasien.csv')
    diagnosis_df = pd.read_csv('models/data_diagnosis_penyakit.csv')
    obat_df = pd.read_csv('models/data_resep_obat.csv')
    logging.info("Datasets loaded successfully.")
except Exception as e:
    logging.error("Error loading datasets: %s", e)
    raise e

# Validasi kolom yang diperlukan
required_columns_diagnosis = ['Diagnosis']
required_columns_obat = ['Diagnosis', 'Resep Obat']
for col in required_columns_diagnosis:
    if col not in diagnosis_df.columns:
        raise ValueError(f"Column '{col}' not found in diagnosis dataset.")
for col in required_columns_obat:
    if col not in obat_df.columns:
        raise ValueError(f"Column '{col}' not found in obat dataset.")

# Create TF-IDF Vectorizer for diagnosis
try:
    tfidf = TfidfVectorizer(stop_words='english')
    tfidf_matrix = tfidf.fit_transform(diagnosis_df['Diagnosis'])
    logging.info("TF-IDF Vectorizer created successfully.")
except Exception as e:
    logging.error("Error creating TF-IDF Vectorizer: %s", e)
    raise e

# Function to get recommendations based on diagnosis text
def get_recommendations(diagnosis_text):
    try:
        logging.debug("Getting recommendations for diagnosis: %s", diagnosis_text)

        # Convert input diagnosis to lowercase
        diagnosis_text_lower = diagnosis_text.lower()

        # Convert the 'Diagnosis' column in the dataset to lowercase for comparison
        diagnosis_df['Diagnosis_Lower'] = diagnosis_df['Diagnosis'].str.lower()

        # Check if the diagnosis is in the dataset (case-insensitive)
        if diagnosis_text_lower not in diagnosis_df['Diagnosis_Lower'].values:
            return {'error': 'Diagnosis tidak ada dalam dataset'}

        # Transform the diagnosis text to TF-IDF
        tfidf_diagnosis = tfidf.transform([diagnosis_text])

        # Calculate cosine similarity between the input diagnosis and all diagnoses in the dataset
        cosine_sim_diagnosis = cosine_similarity(tfidf_diagnosis, tfidf_matrix).flatten()

        # Find the index of the most similar diagnosis
        top_index = cosine_sim_diagnosis.argsort()[-2]  # Get the index of the highest similarity score, excluding the input itself
        matched_diagnosis = diagnosis_df.iloc[top_index]

        # Find the recommended medicine based on the matched diagnosis
        recommended_obat = obat_df[obat_df['Diagnosis'] == matched_diagnosis['Diagnosis']]
        if recommended_obat.empty:
            return {'error': 'Tidak ada rekomendasi obat untuk diagnosis ini'}

        return recommended_obat.iloc[0].to_dict()
    except Exception as e:
        logging.error("Error in get_recommendations: %s", e)
        return {'error': str(e)}


# Route to get recommendations
@app.route('/recommend', methods=['POST'])
def recommend():
    try:
        data = request.get_json()
        logging.debug("Received data: %s", data)
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


# Route to get diagnosis for autocomplete
@app.route('/api/diagnosis', methods=['GET'])
def get_diagnosis():
    try:
        # Ambil query dari parameter URL
        query = request.args.get('q', '').lower()

        # Filter diagnosis berdasarkan query
        filtered_diagnosis = diagnosis_df[diagnosis_df['Diagnosis'].str.contains(query, case=False, na=False)]
        diagnosis_list = filtered_diagnosis['Diagnosis'].tolist()

        logging.debug("Diagnosis results for query '%s': %s", query, diagnosis_list)

        return jsonify(diagnosis_list)
    except Exception as e:
        logging.error("Error in get_diagnosis: %s", e)
        return jsonify({'error': 'An error occurred while fetching diagnosis data.'}), 500


# Route to get resep obat for autocomplete
@app.route('/api/resep-obat', methods=['GET'])
def get_resep_obat():
    try:
        # Ambil query dari parameter URL
        query = request.args.get('q', '').lower()

        # Filter resep obat berdasarkan query
        filtered_resep = obat_df[obat_df['Resep Obat'].str.contains(query, case=False, na=False)]
        resep_list = filtered_resep['Resep Obat'].tolist()

        logging.debug("Resep Obat results for query '%s': %s", query, resep_list)

        return jsonify(resep_list)
    except Exception as e:
        logging.error("Error in get_resep_obat: %s", e)
        return jsonify({'error': 'An error occurred while fetching resep obat data.'}), 500

# Route to get resep obat by diagnosis
@app.route('/api/resep-by-diagnosis', methods=['GET'])
def get_resep_by_diagnosis():
    try:
        # Ambil diagnosis dari parameter URL
        diagnosis = request.args.get('diagnosis', '').lower()

        # Filter data resep obat berdasarkan diagnosis yang dipilih
        filtered_resep = obat_df[obat_df['Diagnosis'].str.lower() == diagnosis]

        if filtered_resep.empty:
            return jsonify({'error': 'Diagnosis tidak ada dalam dataset'}), 400

        # Ambil daftar resep obat yang terkait
        resep_list = filtered_resep['Resep Obat'].tolist()

        logging.debug("Resep Obat for diagnosis '%s': %s", diagnosis, resep_list)

        return jsonify(resep_list)
    except Exception as e:
        logging.error("Error in get_resep_by_diagnosis: %s", e)
        return jsonify({'error': 'An error occurred while fetching resep obat data.'}), 500


if __name__ == '__main__':
    app.run(debug=True)
