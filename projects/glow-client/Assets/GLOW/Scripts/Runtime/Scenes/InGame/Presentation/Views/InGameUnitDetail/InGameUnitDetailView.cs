using System;
using GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail
{
    public class InGameUnitDetailView : UIView
    {
        [SerializeField] InGameUnitNameComponent _unitNameComponent;
        [SerializeField] InGameUnitDetailInfoComponent _infoComponent;
        [SerializeField] InGameUnitDetailSpecialAttackComponent _specialAttackComponent;
        [SerializeField] InGameUnitDetailAbilityComponent _abilityComponent;
        [SerializeField] InGameUnitDetailStatusComponent _statusComponent;
        [SerializeField] InGameUnitDetailTouchLayerComponent _touchLayerComponent;

        public void Setup(InGameUnitDetailViewModel viewModel, BattleStateEffectViewManager battleStateEffectViewManager)
        {
            _unitNameComponent.Setup(viewModel.Info.Name, viewModel.Info.IconViewModel.Rarity);
            _infoComponent.Setup(viewModel.Info, battleStateEffectViewManager);
            _specialAttackComponent.Setup(viewModel.SpecialAttack);
            _abilityComponent.Setup(viewModel.AbilityList);
            _statusComponent.Setup(viewModel.Status);
        }

        public void SetCloseAction(Action onClose)
        {
            _touchLayerComponent.OnTouch = onClose;
        }
    }
}
