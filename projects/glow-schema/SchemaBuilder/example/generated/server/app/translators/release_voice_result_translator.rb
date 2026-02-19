class ReleaseVoiceResultTranslator
  def self.translate(release_voice_result_model)
    view_model = ReleaseVoiceResultViewModel.new
    view_model.crystal = release_voice_result_model.crystal
    view_model
  end
end
