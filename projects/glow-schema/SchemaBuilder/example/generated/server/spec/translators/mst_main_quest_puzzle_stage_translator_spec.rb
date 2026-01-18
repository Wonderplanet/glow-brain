require 'rails_helper'

RSpec.describe "MstMainQuestPuzzleStageTranslator" do
  subject { MstMainQuestPuzzleStageTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, tp: 1, user_exp: 2, entry: 3, main: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMainQuestPuzzleStageViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.tp).to eq use_case_data.tp
      expect(view_model.user_exp).to eq use_case_data.user_exp
      expect(view_model.entry).to eq use_case_data.entry
      expect(view_model.main).to eq use_case_data.main
    end
  end
end
