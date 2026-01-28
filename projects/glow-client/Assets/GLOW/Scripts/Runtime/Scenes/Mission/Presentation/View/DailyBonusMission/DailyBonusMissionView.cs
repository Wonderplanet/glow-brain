using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.DailyBonusMission
{
    public class DailyBonusMissionView : UIView
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct DailyBonusCell
        {
            public int LoginDayCount;
            public DailyBonusMissionCellComponent DailyBonusMissionCellComponent;
        }
        [SerializeField] List<DailyBonusCell> _dailyBonusCellComponents;
        [SerializeField] UIText _dailyBonusUpdateTimeText;

        public void SetUp(
            IDailyBonusMissionViewModel viewModel, 
            Action<PlayerResourceIconViewModel> onRewardIconSelected)
        {
            foreach (var dailyBonusCell in _dailyBonusCellComponents)
            {
                var cell = dailyBonusCell;
                var dailyBonusMissionCellViewModel = viewModel.DailyBonusMissionCellViewModels.Find(model => model.LoginDayCount.Value == cell.LoginDayCount);
                dailyBonusCell.DailyBonusMissionCellComponent.SetUpDailyBonusMissionCell(dailyBonusMissionCellViewModel, onRewardIconSelected);
            }
        }

        public void UpdateTime(RemainingTimeSpan nextUpdateTime)
        {
            _dailyBonusUpdateTimeText.SetText(TimeSpanFormatter.FormatRemaining(nextUpdateTime));
        }
        
        public async UniTask PlayDailyBonusStampAnimationAsync(
            CancellationToken cancellationToken, 
            LoginDayCount loginDayCount)
        {
            var dailyBonusCell = _dailyBonusCellComponents.Find(cell => cell.LoginDayCount == loginDayCount.Value);
            await dailyBonusCell.DailyBonusMissionCellComponent.PlayDailyBonusStampAnimationAsync(cancellationToken);
        }
    }
}
