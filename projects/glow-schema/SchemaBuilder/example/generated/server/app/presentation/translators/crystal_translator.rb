class CrystalTranslator
  def self.translate(crystal_model)
    view_model = CrystalViewModel.new
    view_model.free_amount = crystal_model.free_amount
    view_model.paid_amount = crystal_model.paid_amount
    view_model
  end
end
