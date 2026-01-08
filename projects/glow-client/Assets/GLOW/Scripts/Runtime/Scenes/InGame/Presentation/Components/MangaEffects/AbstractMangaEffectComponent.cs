using System;
using System.Collections.Generic;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class AbstractMangaEffectComponent : UIObject
    {
        readonly MultipleSwitchController _pauseController = new ();
        FieldViewPagePositionTracker _positionTracker;

        protected List<MangaEffectElement> MangaEffectElements = new();

        public Action OnCompleted { get; set; }

        protected override void Awake()
        {
            base.Awake();
            _pauseController.OnStateChanged = OnPause;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _pauseController.Dispose();
        }

        void Update()
        {
            TrackPosition();
        }

        public virtual void Destroy()
        {
        }

        public virtual void Setup(
            MangaEffectLayer mangaEffectLayer,
            FieldViewCoordV2 pos,
            bool isFlip,
            ICoordinateConverter coordinateConverter,
            IViewCoordinateConverter viewCoordinateConverter,
            float pageWidth)
        {
            var fieldCoordPos = viewCoordinateConverter.ToFieldCoord(pos);
            var pageCoordPos = coordinateConverter.FieldToPageCoord(fieldCoordPos);

            var effectPos = new Vector2(pageCoordPos.X * pageWidth, pageCoordPos.Y * pageWidth);
            RectTransform.anchoredPosition = effectPos * -1f;

            if (isFlip)
            {
                Flip();
            }
        }

        public virtual AbstractMangaEffectComponent Play()
        {
            return this;
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        public void SetPositionTracker(FieldViewPagePositionTracker positionTracker)
        {
            _positionTracker = positionTracker;
        }

        protected virtual void OnPause(bool pause)
        {
        }
        
        protected bool IsPaused()
        {
            return _pauseController.IsOn();
        }

        protected T InstantiateMangaEffectElement<T>(T prefab, Transform parent) where T : MangaEffectElement
        {
            var instance = Instantiate(prefab, parent);
            MangaEffectElements.Add(instance);
            return instance;
        }

        void TrackPosition()
        {
            if (_positionTracker == null) return;
            if (_positionTracker.IsTargetDestroyed()) return;

            RectTransform.anchoredPosition = _positionTracker.GetPageUIPos();
        }

        void Flip()
        {
            foreach (var element in MangaEffectElements)
            {
                element.Flip();
            }
        }
    }
}
