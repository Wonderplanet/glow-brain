require 'rails_helper'

RSpec.describe "PuzzleResultUserTranslator" do
  subject { PuzzleResultUserTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_exp: 0, rank_level: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultUserViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_exp).to eq use_case_data.user_exp
      expect(view_model.rank_level).to eq use_case_data.rank_level
    end
  end
end
