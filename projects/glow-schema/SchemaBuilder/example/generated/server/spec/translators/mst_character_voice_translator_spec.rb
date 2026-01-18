require 'rails_helper'

RSpec.describe "MstCharacterVoiceTranslator" do
  subject { MstCharacterVoiceTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_character_id: 1, name: 2, text: 3, voice_key: 4, list_display_type: 5, release_condition: 6, release_at: 7, releasable_start_at: 8, releasable_end_at: 9, sort_number: 10)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstCharacterVoiceViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.text).to eq use_case_data.text
      expect(view_model.voice_key).to eq use_case_data.voice_key
      expect(view_model.list_display_type).to eq use_case_data.list_display_type
      expect(view_model.release_condition).to eq use_case_data.release_condition
      expect(view_model.release_at).to eq use_case_data.release_at
      expect(view_model.releasable_start_at).to eq use_case_data.releasable_start_at
      expect(view_model.releasable_end_at).to eq use_case_data.releasable_end_at
      expect(view_model.sort_number).to eq use_case_data.sort_number
    end
  end
end
