export const generateOrderId = () => {
    const prefix = "HB";

    // Get current year (last 2 digits)
    const today = new Date();
    const year = String(today.getFullYear()).slice(-2); // Last two digits of the year (e.g., "25")

    // Retrieve the last order count and year from localStorage
    const lastOrderCount = localStorage.getItem("lastOrderCount");
    const lastYear = localStorage.getItem("lastOrderYear");

    let count = 1; // Start with 1 by default

    if (lastYear === year && lastOrderCount) {
        // If the stored year matches current year, increment the count
        count = parseInt(lastOrderCount, 10) + 1;
    }

    // Format the count with leading zeros (e.g., "001")
    const formattedCount = String(count).padStart(3, "0");

    // Save the new count and year in localStorage
    localStorage.setItem("lastOrderCount", count);
    localStorage.setItem("lastOrderYear", year);

    // Return the generated order ID in format: HB/25/001
    return `${prefix}/${year}/${formattedCount}`;
};
