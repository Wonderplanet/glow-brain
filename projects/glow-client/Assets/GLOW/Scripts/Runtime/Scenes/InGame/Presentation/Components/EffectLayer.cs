using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class EffectLayer : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class EffectInfo
        {
            public PageEffectId Id;
            public UIEffectComponent Prefab;
        }

        [SerializeField] List<EffectInfo> _effectInfos;

        IViewCoordinateConverter _viewCoordinateConverter;
        ICoordinateConverter _coordinateConverter;

        float _pageWidth;
        List<UIEffectComponent> _effects = new ();

        public void InitializeEffectLayer(
            float pageWidth,
            IViewCoordinateConverter viewCoordinateConverter,
            ICoordinateConverter coordinateConverter)
        {
            _pageWidth = pageWidth;
            _viewCoordinateConverter = viewCoordinateConverter;
            _coordinateConverter = coordinateConverter;
        }

        public UIEffectComponent Generate(PageEffectId id, FieldViewCoordV2 pos)
        {
            var prefab = GetEffectPrefab(id);
            if (prefab == null) return null;

            UIEffectComponent effect = Instantiate(prefab, RectTransform);
            _effects.Add(effect);

            effect.OnCompleted = () => _effects.Remove(effect);

            var fieldCoordPos = _viewCoordinateConverter.ToFieldCoord(pos);
            var pageCoordPos = _coordinateConverter.FieldToPageCoord(fieldCoordPos);

            var effectPos = new Vector2(pageCoordPos.X * _pageWidth, pageCoordPos.Y * _pageWidth);
            effect.RectTransform.anchoredPosition = effectPos * -1f;

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

        UIEffectComponent GetEffectPrefab(PageEffectId id)
        {
            var info = _effectInfos.Find(info => info.Id == id);
            return info?.Prefab;
        }
    }
}
