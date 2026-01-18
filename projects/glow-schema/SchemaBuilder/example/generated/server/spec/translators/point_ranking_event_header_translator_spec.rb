require 'rails_helper'

RSpec.describe "PointRankingEventHeaderTranslator" do
  subject { PointRankingEventHeaderTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, point: 0, rank: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(PointRankingEventHeaderViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.point).to eq use_case_data.point
      expect(view_model.rank).to eq use_case_data.rank
    end
  end
end
