using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    public interface IBeginnerMissionMainViewDelegate
    {
        void OnViewDidLoad();
        void BulkReceive();
        void PlayReceiveAnimationAfterReceivedPoints();
        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition);
        void OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel);
        void OnDayNumberTabSelected(BeginnerMissionDayNumber dayNumber);
        void OnEscape();
    }
}