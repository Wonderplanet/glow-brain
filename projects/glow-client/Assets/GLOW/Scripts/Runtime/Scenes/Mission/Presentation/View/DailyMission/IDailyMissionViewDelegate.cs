using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.DailyMission
{
    public interface IDailyMissionViewDelegate
    {
        void OnViewDidLoad();
        void ReceiveAchievedMissionRewards(MasterDataId dailyMissionId);
        void BulkReceive();
        void OnChallenge(DestinationScene destination);
        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition);
        void OnMissionBonusPointSelected();
        void OnEscape();
    }
}
