require 'rails_helper'

RSpec.describe "QuestSessionTranslator" do
  subject { QuestSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, category: 0, quest_id: 1, mst_or_opr_quest_id: 2, puzzle: 3, status: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(QuestSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.category).to eq use_case_data.category
      expect(view_model.quest_id).to eq use_case_data.quest_id
      expect(view_model.mst_or_opr_quest_id).to eq use_case_data.mst_or_opr_quest_id
      expect(view_model.puzzle).to eq use_case_data.puzzle
      expect(view_model.status).to eq use_case_data.status
    end
  end
end
