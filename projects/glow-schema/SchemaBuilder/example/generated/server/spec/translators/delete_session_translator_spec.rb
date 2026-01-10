require 'rails_helper'

RSpec.describe "DeleteSessionTranslator" do
  subject { DeleteSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, reward_type: 0, present_box: 1, updated_mission: 2, mst_released_music_ids: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(DeleteSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.reward_type).to eq use_case_data.reward_type
      expect(view_model.present_box).to eq use_case_data.present_box
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
      expect(view_model.mst_released_music_ids).to eq use_case_data.mst_released_music_ids
    end
  end
end
