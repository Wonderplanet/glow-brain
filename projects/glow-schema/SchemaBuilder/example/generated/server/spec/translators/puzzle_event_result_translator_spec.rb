require 'rails_helper'

RSpec.describe "PuzzleEventResultTranslator" do
  subject { PuzzleEventResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, base_add_event_point: 0, obtain_event_point_percentage: 1, point_up_time_percentage: 2, obtained_event_point: 3, result_event_point: 4, extended_prizes: 5)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleEventResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.base_add_event_point).to eq use_case_data.base_add_event_point
      expect(view_model.obtain_event_point_percentage).to eq use_case_data.obtain_event_point_percentage
      expect(view_model.point_up_time_percentage).to eq use_case_data.point_up_time_percentage
      expect(view_model.obtained_event_point).to eq use_case_data.obtained_event_point
      expect(view_model.result_event_point).to eq use_case_data.result_event_point
      expect(view_model.extended_prizes).to eq use_case_data.extended_prizes
    end
  end
end
