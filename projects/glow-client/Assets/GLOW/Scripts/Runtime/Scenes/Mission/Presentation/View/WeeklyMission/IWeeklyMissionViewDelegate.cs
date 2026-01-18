using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.WeeklyMission
{
    public interface IWeeklyMissionViewDelegate
    {
        void OnViewDidLoad();
        void ReceiveBonusPoint(MasterDataId weeklyMissionId);
        void BulkReceive();
        void OnChallenge(DestinationScene destination);
        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition);
        void OnMissionBonusPointSelected();
        void OnEscape();
    }
}
