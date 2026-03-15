using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter.Component
{
    public class ArtworkGradeUpCostItemComponent : UIObject
    {
        [SerializeField] PlayerResourceIconButtonComponent _itemIconButtonComponent;
        [SerializeField] ItemPossessionComponent _itemPossessionComponent;

        public void Setup(
            PlayerResourceIconViewModel iconViewModel,
            ItemAmount possessionAmount,
            ItemAmount consumeAmount,
            Action onIconTapped)
        {
            _itemIconButtonComponent.Setup(iconViewModel, onIconTapped);
            _itemPossessionComponent.SetupItem(possessionAmount, consumeAmount);
        }
    }
}
