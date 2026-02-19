require 'rails_helper'

RSpec.describe "HomeMusicListTranslator" do
  subject { HomeMusicListTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, is_random: 0, mst_music_ids: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(HomeMusicListViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.is_random).to eq use_case_data.is_random
      expect(view_model.mst_music_ids).to eq use_case_data.mst_music_ids
    end
  end
end
