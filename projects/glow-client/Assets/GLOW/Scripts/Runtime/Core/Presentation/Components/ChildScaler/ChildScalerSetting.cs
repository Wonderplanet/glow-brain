using System;
using System.Diagnostics.CodeAnalysis;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    [Serializable]
    [SuppressMessage("ReSharper", "InconsistentNaming")]
    public class ChildScalerSetting
    {
        [Header("共通設定")] 
        public float Interval = 0.2f;
        public float ScaleDuration = 1f;

        [Header("スケールアニメーション")] 
        public Vector2 InitialScale = Vector2.one;
        public Vector2 TargetScale = Vector2.one * 2;
        public AnimationCurve ScaleCurve = AnimationCurve.EaseInOut(0, 0, 1, 1);

        [Header("CanvasGroup フェード")] 
        public AnimationCurve AlphaCurve = AnimationCurve.EaseInOut(0, 0, 1, 1);       

        public ChildScalerSetting Clone()
        {
            return new ChildScalerSetting
            {
                Interval = this.Interval,
                ScaleDuration = this.ScaleDuration,
                InitialScale = this.InitialScale,
                TargetScale = this.TargetScale,
                ScaleCurve = new AnimationCurve(this.ScaleCurve.keys),
                AlphaCurve = new AnimationCurve(this.AlphaCurve.keys)
            };
        }
    }
}