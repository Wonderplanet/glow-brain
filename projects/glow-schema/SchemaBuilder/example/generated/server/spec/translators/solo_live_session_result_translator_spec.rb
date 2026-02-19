require 'rails_helper'

RSpec.describe "SoloLiveSessionResultTranslator" do
  subject { SoloLiveSessionResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, obtained_tp: 0, before_user: 1, after_user: 2, before_characters: 3, after_characters: 4, drop: 5, sent_present_box_flg: 6, after_rank: 7, after_point_up_score: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveSessionResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.obtained_tp).to eq use_case_data.obtained_tp
      expect(view_model.before_user).to eq use_case_data.before_user
      expect(view_model.after_user).to eq use_case_data.after_user
      expect(view_model.before_characters).to eq use_case_data.before_characters
      expect(view_model.after_characters).to eq use_case_data.after_characters
      expect(view_model.drop).to eq use_case_data.drop
      expect(view_model.sent_present_box_flg).to eq use_case_data.sent_present_box_flg
      expect(view_model.after_rank).to eq use_case_data.after_rank
      expect(view_model.after_point_up_score).to eq use_case_data.after_point_up_score
    end
  end
end
