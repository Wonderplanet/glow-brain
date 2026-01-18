using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaComponent : UIObject
    {
        public enum KomaLayout
        {
            RightTop,
            MiddleTop,
            LeftTop,
            WholeTop,
            RightMiddle,
            Middle,
            LeftMiddle,
            WholeMiddle,
            RightBottom,
            MiddleBottom,
            LeftBottom,
            WholeBottom,
        }

        [SerializeField] KomaFieldImageComponent _fieldImageComponent;
        [SerializeField] KomaFieldImageComponent _expandedFieldImageComponent;  // コマをはみ出してキャラを表示するとき用
        [SerializeField] KomaBackgroundComponent _backgroundComponent;
        [SerializeField] KomaEffectComponent _backgroundKomaEffectComponent;
        [SerializeField] KomaEffectComponent _frontKomaEffectComponent;
        [SerializeField] UIObject _grayOutImage;
        [SerializeField] KomaFrameComponent _frame;
        [SerializeField] UIShakeComponent _shakeComponent;

        KomaSetComponent _parentKomaSetComponent;
        KomaLayout _komaLayout;
        CoordinateRange _komaRangeOnFieldViewCoord; // FieldView座標系上でのコマの範囲
        bool _isFieldImageExpanding;

        public KomaId KomaId { get; private set; }
        public KomaSetComponent ParentKomaSetComponent => _parentKomaSetComponent;
        public RectTransform BattleFieldRawImageRectTransform => _fieldImageComponent.RectTransform;
        public CoordinateRange KomaRangeOnFieldViewCoord => _komaRangeOnFieldViewCoord;

        void Update()
        {
            if (!_isFieldImageExpanding)
            {
                _fieldImageComponent.UpdateKomaFieldImage(out var isUvUpdated);

                if (isUvUpdated)
                {
                    _backgroundComponent.UpdatePosAndScale(
                        _fieldImageComponent.CurrentUvRect,
                        _fieldImageComponent.InitialUvRect);
                }
            }
        }

        public void InitializeKoma(
            KomaId komaId,
            KomaSetComponent parentKomaSetComponent,
            KomaLayout komaLayout,
            Sprite komaBackgroundSprite,
            KomaBackgroundOffset komaBackgroundOffset,
            KomaEffectType komaEffectType,
            RenderTexture battleFieldRenderTexture,
            Rect komaUVRect,
            CoordinateRange komaRangeOnFieldViewCoord)
        {
            KomaId = komaId;
            _parentKomaSetComponent = parentKomaSetComponent;
            _komaLayout = komaLayout;
            _komaRangeOnFieldViewCoord = komaRangeOnFieldViewCoord;

            _backgroundComponent.Setup(
                komaBackgroundSprite,
                komaBackgroundOffset,
                RectTransform,
                _parentKomaSetComponent.RectTransform.rect.width);

            _fieldImageComponent.Setup(battleFieldRenderTexture, komaUVRect);

            SetupExpandedFieldImageComponent(battleFieldRenderTexture, komaUVRect);

            InstantiateKomaEffect(komaEffectType);
        }
        
        public void SetUpKoma(KomaModel komaModel)
        {
            if (komaModel.ExistsKomaEffects())
            {
                _backgroundKomaEffectComponent.SetUpKomaEffect(komaModel.KomaEffects[0]);
                _frontKomaEffectComponent.SetUpKomaEffect(komaModel.KomaEffects[0]);
            }
        }

        public void UpdateKoma(KomaModel komaModel)
        {
            if (komaModel.ExistsKomaEffects())
            {
                _backgroundKomaEffectComponent.UpdateKomaEffect(komaModel.KomaEffects[0]);
                _frontKomaEffectComponent.UpdateKomaEffect(komaModel.KomaEffects[0]);
            }
        }

        public void ResetKoma(KomaModel komaModel)
        {
            if (komaModel.ExistsKomaEffects())
            {
                _backgroundKomaEffectComponent.ResetKomaEffect(komaModel.KomaEffects[0]);
                _frontKomaEffectComponent.ResetKomaEffect(komaModel.KomaEffects[0]);
            }
        }

        public void StartKomaSelection(bool isSelectable)
        {
            _frame.StartGlow();
            _frame.SetGlowVisible(isSelectable);

            _grayOutImage.Hidden = isSelectable;
        }

        public void EndKomaSelection()
        {
            _frame.StopGlow();
            _grayOutImage.Hidden = true;
        }

        public void SetKomaSelectable(bool isSelectable)
        {
            _frame.SetGlowVisible(isSelectable);
            _grayOutImage.Hidden = isSelectable;
        }

        public void ShowSpecialUnitSpecialAttackInEffectRange(bool isInRange)
        {
            _frame.StartSpecialAttackCoordinateRangeGlow();
            _frame.SetSpecialAttackCoordinateRangeGlowVisible(isInRange);
            _grayOutImage.Hidden = isInRange;
        }

        public void HideSpecialUnitSpecialAttackInEffectRange()
        {
            _frame.StopSpecialAttackCoordinateRangeGlow();
            _grayOutImage.Hidden = true;
        }

        public void InstantiateKomaEffect(KomaEffectType komaEffectType)
        {
            _backgroundKomaEffectComponent.InstantiateKomaEffect(komaEffectType);
            _frontKomaEffectComponent.InstantiateKomaEffect(komaEffectType);
        }

        public bool ContainsAt(FieldViewCoordV2 pos)
        {
            return _komaRangeOnFieldViewCoord.IsInRange(pos.X);
        }

        public void Shake(float duration)
        {
            _shakeComponent.Shake(duration);
        }

        public MultipleSwitchHandler StartShake(MultipleSwitchHandler handler)
        {
            return _shakeComponent.StartShake(handler);
        }

        public void StartTracking(FieldViewPositionTracker tracker)
        {
            _fieldImageComponent.StartTracking(tracker);
        }

        public void EndTracking()
        {
            _fieldImageComponent.EndTracking();
        }

        public void ExpandFieldImage(bool isExpanded)
        {
            if (!IsDarknessCleared())
            {
                return;
            }

            _isFieldImageExpanding = isExpanded;

            _fieldImageComponent.Hidden = isExpanded;
            _expandedFieldImageComponent.Hidden = !isExpanded;
        }

        void SetupExpandedFieldImageComponent(RenderTexture fieldRenderTexture, Rect komaUVRect)
        {
            var expandedKomaUVRect = komaUVRect;
            expandedKomaUVRect.yMax = Mathf.Max(1f, komaUVRect.height);

            _expandedFieldImageComponent.Setup(fieldRenderTexture, expandedKomaUVRect);
            _expandedFieldImageComponent.Hidden = true;
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            if (_frontKomaEffectComponent != null)
            {
                _frontKomaEffectComponent.Pause(handler);
            }

            if (_backgroundKomaEffectComponent != null)
            {
                _backgroundKomaEffectComponent.Pause(handler);
            }

            _shakeComponent.Pause(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutDarknessClear(MultipleSwitchHandler handler)
        {
            if (_frontKomaEffectComponent != null)
            {
                _frontKomaEffectComponent.PauseWithoutDarknessClear(handler);
            }

            if (_backgroundKomaEffectComponent != null)
            {
                _backgroundKomaEffectComponent.PauseWithoutDarknessClear(handler);
            }

            return handler;
        }

        public bool IsDarknessCleared()
        {
            return _frontKomaEffectComponent.IsDarknessCleared();
        }

        Vector2 GetScalingPivot(FieldViewCoordV2 targetPos)
        {
            bool targetIsRight = targetPos.X > _komaRangeOnFieldViewCoord.Center;

            return _komaLayout switch
            {
                KomaLayout.RightTop => new Vector2(1f, 1f),
                KomaLayout.MiddleTop => new Vector2(0.5f, 1f),
                KomaLayout.LeftTop => new Vector2(0f, 1f),
                KomaLayout.WholeTop => targetIsRight ? new Vector2(1f, 1f) : new Vector2(0f, 1f),
                KomaLayout.RightMiddle => new Vector2(1f, 0.5f),
                KomaLayout.Middle => new Vector2(0.5f, 0.5f),
                KomaLayout.LeftMiddle => new Vector2(0f, 0.5f),
                KomaLayout.WholeMiddle => targetIsRight ? new Vector2(1f, 0.5f) : new Vector2(0f, 0.5f),
                KomaLayout.RightBottom => new Vector2(1f, 0f),
                KomaLayout.MiddleBottom => new Vector2(0.5f, 0f),
                KomaLayout.LeftBottom => new Vector2(0f, 0f),
                KomaLayout.WholeBottom => targetIsRight ? new Vector2(1f, 0f) : new Vector2(0f, 0f),
                _ => Vector2.zero,
            };
        }
    }
}
