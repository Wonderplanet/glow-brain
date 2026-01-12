using System.Collections.Generic;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.DailyBonusMission
{
    public interface IDailyBonusMissionViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition);
        void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnEscape();
    }
}
