using System;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.AchievementMission
{
    public class AchievementMissionView : UIView
    {
        [SerializeField]  UICollectionView _collectionView;

        public UICollectionView CollectionView => _collectionView;

        Action _onApplicationFocusedAction;
        
        public void SetOnApplicationFocusedAction(Action action)
        {
            _onApplicationFocusedAction = action;
        }
        
        public void ClearOnApplicationFocusedAction()
        {
            _onApplicationFocusedAction = null;
        }

        void OnApplicationFocus(bool hasFocus)
        {
            if (hasFocus)
            {
                _onApplicationFocusedAction?.Invoke();
            }
        }
    }
}