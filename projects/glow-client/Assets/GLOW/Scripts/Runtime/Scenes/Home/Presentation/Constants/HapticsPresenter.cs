using System;
using Wonderplanet.UIHaptics;
using Wonderplanet.UIHaptics.Presentation;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Constants
{
    public class HapticsPresenter : IHapticsPresenter
        , IInitializable
        , IDisposable
    {
        UIImpactFeedback.Style _defaultStyle = UIImpactFeedback.Style.Light;
        bool _initUIImpactFeedbackUsable;

        void Awake()
        {
            Initialize(UIImpactFeedback.Style.Light);
        }
        void IInitializable.Initialize()
        {
            Initialize(UIImpactFeedback.Style.Light);
        }

        public void Initialize(UIImpactFeedback.Style defaultStyle)
        {
            if (UIImpactFeedback.CheckSupported())
            {
                UIImpactFeedback.Initialize();
                this._defaultStyle = defaultStyle;
                _initUIImpactFeedbackUsable = true;
            }
            else
            {
                _initUIImpactFeedbackUsable = false;
            }
        }

        public void SyncRestartEngine()
        {
            if (UIImpactFeedback.CheckSupported())
            {
                UIImpactFeedback.Initialize();
            }
        }
        public void RestartEngine()
        {
            if (UIImpactFeedback.CheckSupported())
            {
                UIImpactFeedback.RestartEngine();
            }
        }

        public void Impact()
        {
            if (_initUIImpactFeedbackUsable)
            {
                UIImpactFeedback.Generate(_defaultStyle);
                // UIImpactFeedback.Prepare(defaultStyle); //次の利用に備えてPrepareする
            }
            else
            {
                VibrationMng.ShortVibration();
            }
        }

        public void Impact(UIImpactFeedback.Style style)
        {
            var useUIImpactFeedback = style == UIImpactFeedback.Style.Light || style == UIImpactFeedback.Style.Medium || style == UIImpactFeedback.Style.Heavy;
            if (_initUIImpactFeedbackUsable && useUIImpactFeedback)
            {
                UIImpactFeedback.Generate(style);
                // UIImpactFeedback.Prepare(style); //次の利用に備えてPrepareする
            }
            else
            {
                VibrationMng.ShortVibration(style);
            }
        }

        void Deinitialize()
        {
            if (UIImpactFeedback.CheckSupported())
            {
                UIImpactFeedback.Deinitialize();
            }
            _initUIImpactFeedbackUsable = false;
        }

        void IDisposable.Dispose()
        {
            Deinitialize();
        }
    }
}
