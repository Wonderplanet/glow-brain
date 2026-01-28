class MasterManifestTranslator
  def self.translate(master_manifest_model)
    view_model = MasterManifestViewModel.new
    view_model.hash = master_manifest_model.hash
    view_model
  end
end
