require 'rails_helper'

RSpec.describe "GachaTranslator" do
  subject { GachaTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_gacha_id: 0, play_count: 1, draw_count: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(GachaViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_gacha_id).to eq use_case_data.opr_gacha_id
      expect(view_model.play_count).to eq use_case_data.play_count
      expect(view_model.draw_count).to eq use_case_data.draw_count
    end
  end
end
