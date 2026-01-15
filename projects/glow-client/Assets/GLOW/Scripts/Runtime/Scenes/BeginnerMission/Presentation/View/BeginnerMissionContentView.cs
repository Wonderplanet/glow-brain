using System;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public class BeginnerMissionContentView : UIView
    {
        [SerializeField] UICollectionView _collectionView;

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