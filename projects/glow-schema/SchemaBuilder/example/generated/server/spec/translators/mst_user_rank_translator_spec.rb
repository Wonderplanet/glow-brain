require 'rails_helper'

RSpec.describe "MstUserRankTranslator" do
  subject { MstUserRankTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, rank_level: 1, required_point: 2, max_ap: 3, max_friend_count: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstUserRankViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.required_point).to eq use_case_data.required_point
      expect(view_model.max_ap).to eq use_case_data.max_ap
      expect(view_model.max_friend_count).to eq use_case_data.max_friend_count
    end
  end
end
