require 'rails_helper'

RSpec.describe "QuestTranslator" do
  subject { QuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_quest_id: 1, play_count: 2, clear_count: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(QuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_quest_id).to eq use_case_data.mst_quest_id
      expect(view_model.play_count).to eq use_case_data.play_count
      expect(view_model.clear_count).to eq use_case_data.clear_count
    end
  end
end
