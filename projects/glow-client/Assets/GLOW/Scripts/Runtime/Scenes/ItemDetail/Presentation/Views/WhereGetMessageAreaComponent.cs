using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    public class WhereGetMessageAreaComponent : UIView
    {
        [Serializable]
        struct UIButtonSet
        {
            public UITextButton Button;
            public UIObject Object;
        }

        [SerializeField] UIButtonSet _mainQuestButton;
        [SerializeField] UIButtonSet _eventQuestButton;
        [SerializeField] UIButtonSet _shopButton;
        [SerializeField] UIButtonSet _missionButton;
        [SerializeField] UIButtonSet _explorationButton;
        [SerializeField] UIButtonSet _exhangeShopButton;
        [SerializeField] UIObject _currentObject;

        public bool Hidden
        {
            set { _currentObject.Hidden = value; }
        }

        public void InitializeView()
        {
            _mainQuestButton.Object.Hidden = true;
            _eventQuestButton.Object.Hidden = true;
            _shopButton.Object.Hidden = true;
            _missionButton.Object.Hidden = true;
            _explorationButton.Object.Hidden = true;
            _exhangeShopButton.Object.Hidden = true;
        }

        public void EarnLocationSetActive(ItemDetailEarnLocationViewModel earnLocationModel, Action<ItemDetailEarnLocationViewModel, bool> onClicked, bool popBeforeDetail = false)
        {
            switch (earnLocationModel.TransitionType)
            {
                case ItemTransitionType.MainQuest:
                    _mainQuestButton.Object.Hidden = false;
                    _mainQuestButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
                case ItemTransitionType.EventQuest:
                    _eventQuestButton.Object.Hidden = false;
                    _eventQuestButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
                case ItemTransitionType.ShopItem:
                case ItemTransitionType.Pack:
                    _shopButton.Object.Hidden = false;
                    _shopButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
                case ItemTransitionType.Achievement:
                case ItemTransitionType.LoginBonus:
                case ItemTransitionType.DailyMission:
                case ItemTransitionType.WeeklyMission:
                    _missionButton.Object.Hidden = false;
                    _missionButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
                case ItemTransitionType.Patrol:
                    _explorationButton.Object.Hidden = false;
                    _explorationButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
                case ItemTransitionType.ExchangeShop:
                    _exhangeShopButton.Object.Hidden = false;
                    _exhangeShopButton.Button.onClick.AddListener(() => onClicked?.Invoke(earnLocationModel, popBeforeDetail));
                    break;
            }
        }
    }
}
