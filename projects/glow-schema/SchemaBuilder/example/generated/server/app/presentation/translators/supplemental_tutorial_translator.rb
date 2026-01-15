class SupplementalTutorialTranslator
  def self.translate(supplemental_tutorial_model)
    view_model = SupplementalTutorialViewModel.new
    view_model.feature = supplemental_tutorial_model.feature
    view_model
  end
end
