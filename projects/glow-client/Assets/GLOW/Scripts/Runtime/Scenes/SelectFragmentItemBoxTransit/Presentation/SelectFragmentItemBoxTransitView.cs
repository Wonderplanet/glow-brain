using System;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation
{
    public class SelectFragmentItemBoxTransitView : UIView
    {
        [SerializeField] WhereGetMessageAreaComponent _whereGetMessageAreaComponent;

        public void SetUp(
            ItemDetailAvailableLocationViewModel argsAvailableLocationViewModel,
            Action<ItemDetailEarnLocationViewModel, bool> onTapped)
        {
            _whereGetMessageAreaComponent.InitializeView();
            _whereGetMessageAreaComponent.EarnLocationSetActive(
                argsAvailableLocationViewModel.EarnLocationViewModel1, onTapped);
            _whereGetMessageAreaComponent.EarnLocationSetActive(
                argsAvailableLocationViewModel.EarnLocationViewModel2, onTapped);
        }
    }
}
