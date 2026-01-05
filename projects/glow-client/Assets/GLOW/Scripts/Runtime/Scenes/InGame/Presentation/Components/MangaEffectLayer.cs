using System.Collections.Generic;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class MangaEffectLayer : UIObject
    {
        IViewCoordinateConverter _viewCoordinateConverter;
        ICoordinateConverter _coordinateConverter;

        float _pageWidth;
        List<AbstractMangaEffectComponent> _effects = new ();

        public IReadOnlyList<AbstractMangaEffectComponent> Effects => _effects;

        public void Initialize(
            float pageWidth,
            IViewCoordinateConverter viewCoordinateConverter,
            ICoordinateConverter coordinateConverter)
        {
            _pageWidth = pageWidth;
            _viewCoordinateConverter = viewCoordinateConverter;
            _coordinateConverter = coordinateConverter;
        }


        public AbstractMangaEffectComponent Generate(GameObject prefab, FieldViewCoordV2 pos, bool isFlip)
        {
            if (prefab == null) return null;

            var effectGameObject = Instantiate(prefab, RectTransform);

            var effect = effectGameObject.GetComponent<AbstractMangaEffectComponent>();
            if (effect == null)
            {
                Destroy(effectGameObject);
                return null;
            }

            SetupMangaEffect(effect, pos, isFlip);

            return effect;
        }

        public T Generate<T>(T prefab, FieldViewCoordV2 pos, bool isFlip) where T : AbstractMangaEffectComponent
        {
            if (prefab == null) return null;

            var effect = Instantiate(prefab, RectTransform);
            if (effect == null) return null;

            SetupMangaEffect(effect, pos, isFlip);

            return effect;
        }

        public T Generate<T>(T prefab, IFieldViewPagePositionTrackerTarget trackingTarget, bool isFlip) where T : AbstractMangaEffectComponent
        {
            var effect = Generate(prefab, trackingTarget.GetFieldViewCoordPos(), isFlip);

            var tracker = new FieldViewPagePositionTracker(
                trackingTarget,
                _viewCoordinateConverter,
                _coordinateConverter,
                _pageWidth);

            effect.SetPositionTracker(tracker);

            return effect;
        }

        public MultipleSwitchHandler PauseAllEffects(MultipleSwitchHandler handler)
        {
            foreach (var effect in _effects )
            {
                effect.Pause(handler);
            }

            return handler;
        }

        void SetupMangaEffect(AbstractMangaEffectComponent effect, FieldViewCoordV2 pos, bool isFlip)
        {
            _effects.Add(effect);

            effect.OnCompleted = () => _effects.Remove(effect);

            effect.Setup(this, pos, isFlip, _coordinateConverter, _viewCoordinateConverter, _pageWidth);
        }
    }
}
