class RecoverApResultTranslator
  def self.translate(recover_ap_result_model)
    view_model = RecoverApResultViewModel.new
    view_model.user_ap = recover_ap_result_model.user_ap
    view_model.crystal = recover_ap_result_model.crystal
    view_model.item = recover_ap_result_model.item
    view_model
  end
end
