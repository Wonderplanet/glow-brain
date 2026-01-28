require 'rails_helper'

RSpec.describe "CharacterVoiceTranslator" do
  subject { CharacterVoiceTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_character_id: 0, flags: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(CharacterVoiceViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.flags).to eq use_case_data.flags
    end
  end
end
