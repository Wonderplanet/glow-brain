using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public interface IHomeMainKomaSettingViewDelegate
    {
        void OnViewDidLoad();
        void OnUnitEditButtonTapped(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds,
            Action<HomeMainKomaPatternViewModel> onUpdate);
        void OnClose(MasterDataId currentMstKomaPatternId);
        void OnHelpButtonTapped();
        void OnEscape(MasterDataId currentMstKomaPatternId);
    }
}
