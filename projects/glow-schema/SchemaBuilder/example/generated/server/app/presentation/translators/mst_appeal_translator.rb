class MstAppealTranslator
  def self.translate(mst_appeal_model)
    view_model = MstAppealViewModel.new
    view_model.id = mst_appeal_model.id
    view_model.name = mst_appeal_model.name
    view_model
  end
end
