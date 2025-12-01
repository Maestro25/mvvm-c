/**
 * Universal fetch wrapper to handle HTTP and network errors gracefully.
 * @param {string} url - API endpoint URL.
 * @param {object} options - fetch options.
 * @returns {Promise<any>} - resolves with parsed JSON data or rejects with error details.
 */
export async function fetchWithErrorHandling(url, options = {}) {
  try {
    const response = await fetch(url, options);

    // Ignore errors for Chrome DevTools special request
   

    if (!response.ok) {
  const contentType = response.headers.get('Content-Type') || '';
  let errorMessage = `HTTP error ${response.status}`;

  if (contentType.includes('application/json')) {
    const errorData = await response.json();
    errorMessage += `: ${errorData.error || errorData.message || JSON.stringify(errorData)}`;
  } else if (contentType.includes('text/html')) {
    // Optionally, you can log or show HTML as text or a generic error message
    errorMessage += ': Unexpected server error';
  } else {
    const errorText = await response.text();
    if (errorText) errorMessage += `: ${errorText}`;
  }
  throw new Error(errorMessage);
}

    return await response.json();
  } catch (error) {

    

    console.error('Fetch error:', error);
    displayErrorMessage(error.message || 'An error occurred. Please try again later.');
    throw error;
  }
}


/**
 * Helper to display error message to users.
 * Replace with your UI error component or toast implementation.
 * @param {string} message
 */
export function displayErrorMessage(message) {
  let errorContainer = document.getElementById('error-container');
  if (!errorContainer) {
    errorContainer = document.createElement('div');
    errorContainer.id = 'error-container';
    errorContainer.style.position = 'fixed';
    errorContainer.style.bottom = '20px';
    errorContainer.style.right = '20px';
    errorContainer.style.backgroundColor = '#f44336';
    errorContainer.style.color = '#fff';
    errorContainer.style.padding = '15px';
    errorContainer.style.borderRadius = '5px';
    errorContainer.style.boxShadow = '0 2px 10px rgba(0,0,0,0.3)';
    document.body.appendChild(errorContainer);
  }
  errorContainer.textContent = message;
  errorContainer.style.display = 'block';
  setTimeout(() => { errorContainer.style.display = 'none'; }, 5000);
}
