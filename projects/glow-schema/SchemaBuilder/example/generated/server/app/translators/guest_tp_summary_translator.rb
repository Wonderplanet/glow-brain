class GuestTpSummaryTranslator
  def self.translate(guest_tp_summary_model)
    view_model = GuestTpSummaryViewModel.new
    view_model.received = guest_tp_summary_model.received
    view_model.received_history = guest_tp_summary_model.received_history
    view_model.user_count = guest_tp_summary_model.user_count
    view_model
  end
end
