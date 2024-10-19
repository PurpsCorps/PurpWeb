async function printReceipt(orderId) { const response = await fetch(`/orders/${orderId}/print-receipt`);
    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'receipt.pdf';
    a.click();
    URL.revokeObjectURL(url);
}