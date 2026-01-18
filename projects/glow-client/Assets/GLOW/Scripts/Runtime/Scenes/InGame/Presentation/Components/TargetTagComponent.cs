using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Common;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    /// <summary>
    /// ターゲットタグ（敵）
    /// </summary>
    public class TargetTagComponent : UIObject
    {
        [SerializeField] GameObject _bossTag;
        public FieldViewPositionTracker FieldViewPositionTracker { get; private set; }

        public void Initialize(FieldViewPositionTracker fieldViewPositionTracker, bool isBoss)
        {
            FieldViewPositionTracker = fieldViewPositionTracker;
            _bossTag.SetActive(isBoss);
        }

    }
}
