using System.Collections.Generic;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.Pool;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DamageDisplayLayer : UIObject
    {
        [SerializeField] DamageNumberDisplayComponent _damagePrefab;
        
        const int Capacity = 3;
        const int MaxSize = 30;
        
        IViewCoordinateConverter _viewCoordinateConverter;
        ICoordinateConverter _coordinateConverter;
        float _pageWidth;
        
        ObjectPool<DamageNumberDisplayComponent> _damageNumberPool;
        List<DamageNumberDisplayComponent> _activeDamageNumberComponents = new ();
        
        public void InitializeDamageDisplayLayer(
            float pageWidth,
            IViewCoordinateConverter viewCoordinateConverter,
            ICoordinateConverter coordinateConverter)
        {
            _pageWidth = pageWidth;
            _viewCoordinateConverter = viewCoordinateConverter;
            _coordinateConverter = coordinateConverter;
            
            _damageNumberPool = new ObjectPool<DamageNumberDisplayComponent>(
                createFunc: () =>
                {
                    var effect = Instantiate(_damagePrefab, RectTransform);
                    effect.IsVisible = true;
                    return effect;
                },
                actionOnGet: effect =>
                {
                    effect.OnCompleted = () => _damageNumberPool.Release(effect);
                    effect.IsVisible = true;
                    _activeDamageNumberComponents.Add(effect);
                },
                actionOnRelease: effect =>
                {
                    effect.IsVisible = false;
                    _activeDamageNumberComponents.Remove(effect);
                },
                defaultCapacity: Capacity,
                maxSize: MaxSize);
        }
        
        public DamageNumberDisplayComponent Generate(
            FieldViewCoordV2 pos,
            FieldViewCoordV2 offsetPos,
            AppliedAttackResultModel appliedAttackResultModel)
        {
            var effectView = _damageNumberPool.Get();
            var fieldCoordPos = _viewCoordinateConverter.ToFieldCoord(pos + offsetPos);
            var pageCoordPos = _coordinateConverter.FieldToPageCoord(fieldCoordPos);

            var effectPos = new Vector2(pageCoordPos.X * _pageWidth, pageCoordPos.Y * _pageWidth);
            effectView.RectTransform.anchoredPosition = effectPos * -1f;
            effectView.SetDamageText(
                appliedAttackResultModel.Damage,
                appliedAttackResultModel.Heal,
                appliedAttackResultModel.TargetBattleSide,
                appliedAttackResultModel.AttackDamageType,
                appliedAttackResultModel.IsAdvantageColor,
                appliedAttackResultModel.AttackerColor);
            return effectView;
        }
        
        public MultipleSwitchHandler PauseAllEffects(MultipleSwitchHandler handler)
        {
            foreach (var effect in _activeDamageNumberComponents)
            {
                effect.Pause(handler);
            }

            return handler;
        }
        
        public void SetAllActiveEffectsVisible(DamageDisplayFlag isVisible)
        {
            // 現時点で使われているエフェクトの表示・非表示を切り替え
            foreach (var effect in _activeDamageNumberComponents)
            {
                effect.gameObject.SetActive(isVisible);
            }
        } 
    }
}