using System;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.ValueObject;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.Component;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.View
{
    public class BoxGachaLineupDialogView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] BoxGachaLineupListComponent _lineupListComponent;
        [SerializeField] UIObject _unitDetailAttentionTextObject;

        public void SetUpTitle(BoxResetCount boxResetCount)
        {
            _titleText.SetText(ZString.Format("{0}回目いいジャンくじ ラインナップ", boxResetCount.ToCurrentBoxNumber()));
        }

        public void SetUpURLineupList(
            BoxGachaLineupListViewModel boxGachaURLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            _lineupListComponent.SetUpURLineupList(boxGachaURLineupListViewModel, onPrizeIconSelected);
        }
        
        public void SetUpSSRLineupList(
            BoxGachaLineupListViewModel boxGachaSSRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            _lineupListComponent.SetUpSSRLineupList(boxGachaSSRLineupListViewModel, onPrizeIconSelected);
        }
        
        public void SetUpSRLineupList(
            BoxGachaLineupListViewModel boxGachaSRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            _lineupListComponent.SetUpSRLineupList(boxGachaSRLineupListViewModel, onPrizeIconSelected);
        }
        
        public void SetUpRLineupList(
            BoxGachaLineupListViewModel boxGachaRLineupListViewModel,
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            _lineupListComponent.SetUpRLineupList(boxGachaRLineupListViewModel, onPrizeIconSelected);
        }
        
        public void SetUpUnitDetailAttentionTextVisible(UnitContainInLineupFlag flag)
        {
            _unitDetailAttentionTextObject.IsVisible = flag;
        }
    }
}