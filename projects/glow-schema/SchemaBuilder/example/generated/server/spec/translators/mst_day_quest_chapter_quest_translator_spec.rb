require 'rails_helper'

RSpec.describe "MstDayQuestChapterQuestTranslator" do
  subject { MstDayQuestChapterQuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, day_of_week: 1, mst_day_quest_chapter_id: 2, mst_day_quest_id: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstDayQuestChapterQuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.day_of_week).to eq use_case_data.day_of_week
      expect(view_model.mst_day_quest_chapter_id).to eq use_case_data.mst_day_quest_chapter_id
      expect(view_model.mst_day_quest_id).to eq use_case_data.mst_day_quest_id
    end
  end
end
