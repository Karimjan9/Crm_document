<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">
          <i class="fas fa-money-bill-wave me-2"></i>To'lov qilish
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
      </div>

      <form id="paymentForm">
        <div class="modal-body">

          {{-- Hidden --}}
          <input type="hidden" id="document_id" name="document_id">

          <div class="mb-3">
            <label class="form-label">Hujjat raqami</label>
            <input type="text" class="form-control" id="document_code" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Qoldiq</label>
            <input type="text" class="form-control" id="balance" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">To‘lov summasi</label>
            <input type="number" class="form-control" name="amount" min="1" step="1" required placeholder="Masalan: 100000">
            <small class="text-muted">Summani qoldiqdan oshirmang.</small>
          </div>

          <div class="mb-1">
            <label class="form-label">To‘lov turi</label>
            <select class="form-select" name="payment_type" required>
              <option value="Naqd">Naqd</option>
              <option value="Karta">Karta</option>
              <option value="Click">Click</option>
              <option value="Payme">Payme</option>
              <option value="Bank">Bank</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Bekor qilish</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check me-1"></i>Saqlash
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
