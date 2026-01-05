using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Common;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    /// <summary>
    /// ボスタグ
    /// </summary>
    public class BossTagComponent : UIObject
    {
        [SerializeField] UIAnimation _uiAnimation;

        public FieldViewPositionTracker FieldViewPositionTracker { get; private set; }
        public Action OnDestroyed { get; set; }

        public void Initialize(FieldViewPositionTracker fieldViewPositionTracker)
        {
            FieldViewPositionTracker = fieldViewPositionTracker;

            _uiAnimation.OnDone = () =>
            {
                Destroy(gameObject);
                OnDestroyed?.Invoke();
            };

            _uiAnimation.Play();
        }
    }
}
