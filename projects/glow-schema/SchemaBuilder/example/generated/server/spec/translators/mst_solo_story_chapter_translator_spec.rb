require 'rails_helper'

RSpec.describe "MstSoloStoryChapterTranslator" do
  subject { MstSoloStoryChapterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_variant_id: 1, name: 2, release_at: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSoloStoryChapterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_variant_id).to eq use_case_data.mst_character_variant_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.release_at).to eq use_case_data.release_at
    end
  end
end
