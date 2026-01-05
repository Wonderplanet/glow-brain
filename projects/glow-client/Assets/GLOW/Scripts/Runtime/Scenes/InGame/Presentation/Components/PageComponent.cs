using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    [Serializable]
    [SuppressMessage("ReSharper", "InconsistentNaming")]
    public class KomaSetComponentSerializable
    {
        public string AssetPath;
        public KomaSetComponent Component;
    }

    public class PageComponent : UIObject
    {
        [SerializeField] float _pageWidth;
        [SerializeField] ScrollRect _scrollRect;
        [SerializeField] RectTransform _pageRectTransform;
        [SerializeField] RectTransform _pageScaringRootTransform;
        [SerializeField] RectTransform _komaLayerRectTransform;
        [SerializeField] RectTransform _tagLayerRectTransform;
        [SerializeField] RectTransform _hpGaugeLayerRectTransform;
        [SerializeField] MangaEffectLayer _mangaEffectLayer;
        [SerializeField] EffectLayer _effectLayer;
        [SerializeField] DamageDisplayLayer _damageDisplayLayer;
        [SerializeField] PageTouchLayer _pageTouchLayer;
        [SerializeField] BossTagComponent _bossTagPrefab;
        [SerializeField] TargetTagComponent _targetTagPrefab;
        [SerializeField] List<KomaSetComponentSerializable> _komaSetPrefabComponents;
        [Header("Hpゲージ")]
        [SerializeField] FieldUnitConditionComponent _fieldUnitConditionPlayer;
        [SerializeField] FieldUnitConditionComponent _fieldUnitConditionEnemy;
        [SerializeField] FieldUnitConditionComponent _fieldUnitConditionAdventBoss;

        IViewCoordinateConverter _viewCoordinateConverter;
        ICoordinateConverter _coordinateConverter;
        PrefabFactory<KomaSetComponent> _komaSetComponentFactory;
        IKomaBackgroundContainer _komaBackgroundContainer;

        List<KomaSetComponent> _komaSetComponents = new ();
        List<KomaComponent> _komaComponents = new ();
        List<BossTagComponent> _bossTagComponents = new ();
        List<TargetTagComponent> _targetTagComponents = new ();
        Dictionary<FieldObjectId, FieldUnitConditionComponent> _fieldUnitConditionComponentDictionary = new ();

        float _pageHeight;
        float _scrollRectHeight;
        bool _isScrollablePage = true;

        public float PageWidth => _pageWidth;
        public bool IsScrollablePage => _isScrollablePage;

        public bool TouchLayerHidden
        {
            get => _pageTouchLayer.Hidden;
            set => _pageTouchLayer.Hidden = value;
        }

        public Action<PageCoordV2> OnTouch { get; set; }

        public void InitializeBattlePage(
            MstPageModel mstPage,
            BattleFieldView battleFieldView,
            IViewCoordinateConverter viewCoordinateConverter,
            ICoordinateConverter coordinateConverter,
            PrefabFactory<KomaSetComponent> komaSetComponentFactory,
            IKomaBackgroundContainer komaBackgroundContainer)
        {
            _viewCoordinateConverter = viewCoordinateConverter;
            _coordinateConverter = coordinateConverter;
            _komaSetComponentFactory = komaSetComponentFactory;
            _komaBackgroundContainer = komaBackgroundContainer;

            InitializeKoma(mstPage, battleFieldView, out _pageHeight);

            _pageRectTransform.sizeDelta = new Vector2(_pageRectTransform.sizeDelta.x, _pageHeight);
            SetupLayout();

            _pageTouchLayer.OnTouch = OnTouchLayerTouched;

            _mangaEffectLayer.Initialize(_pageWidth, viewCoordinateConverter, coordinateConverter);

            _effectLayer.InitializeEffectLayer(
                _pageWidth,
                viewCoordinateConverter,
                coordinateConverter);
            
            _damageDisplayLayer.InitializeDamageDisplayLayer(
                _pageWidth,
                viewCoordinateConverter,
                coordinateConverter);
        }

        public void SetupLayout()
        {
            // 全体がスクロールエリアに収まる場合はスクロールを無効にする
            var scrollRectRectTransform = _scrollRect.transform as RectTransform;
            if (scrollRectRectTransform == null) return;

            _scrollRectHeight = scrollRectRectTransform.rect.height;
            _isScrollablePage = _pageHeight > _scrollRectHeight;

            if (_isScrollablePage)
            {
                _scrollRect.enabled = true;

                _pageRectTransform.anchorMin = new Vector2(0.5f, 1f);
                _pageRectTransform.anchorMax = new Vector2(0.5f, 1f);
                _pageRectTransform.pivot = new Vector2(0.5f, 1f);
                _pageRectTransform.anchoredPosition = new Vector2(0, 0);
            }
            else
            {
                _scrollRect.enabled = false;

                _pageRectTransform.anchorMin = new Vector2(0.5f, 0.5f);
                _pageRectTransform.anchorMax = new Vector2(0.5f, 0.5f);
                _pageRectTransform.pivot = new Vector2(0.5f, 0.5f);
                _pageRectTransform.anchoredPosition = new Vector2(0, 0);
            }
        }
        
        public void SetUpKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            foreach (var komaComponent in _komaComponents)
            {
                if (!komaDictionary.ContainsKey(komaComponent.KomaId)) continue;

                komaComponent.SetUpKoma(komaDictionary[komaComponent.KomaId]);
            }
        }

        public void UpdateKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            foreach (var komaComponent in _komaComponents)
            {
                if (!komaDictionary.ContainsKey(komaComponent.KomaId)) continue;

                komaComponent.UpdateKoma(komaDictionary[komaComponent.KomaId]);
            }
        }

        public void ResetKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            foreach (var komaComponent in _komaComponents)
            {
                if (!komaDictionary.ContainsKey(komaComponent.KomaId)) continue;

                komaComponent.ResetKoma(komaDictionary[komaComponent.KomaId]);
            }
        }

        public void AddBossTag(FieldViewPositionTracker positionTracker)
        {
            KomaId komaId = positionTracker.GetKomaIdBelongTo();

            KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
            if (komaComponent == null)
            {
                ApplicationLog.LogWarning(nameof(PageComponent), ZString.Format("KomaComponent is not found. {0}", komaId));
                return;
            }

            BossTagComponent bossTag = Instantiate(_bossTagPrefab, _tagLayerRectTransform);
            bossTag.Initialize(positionTracker);

            bossTag.OnDestroyed = () => _bossTagComponents.Remove(bossTag);

            bossTag.RectTransform.anchoredPosition = bossTag.FieldViewPositionTracker.GetUIPosition(
                komaId,
                komaComponent.BattleFieldRawImageRectTransform,
                _tagLayerRectTransform,
                Camera.main);

            _bossTagComponents.Add(bossTag);
        }

        public void AddTargetTag(FieldViewPositionTracker positionTracker, bool isBoss)
        {
            KomaId komaId = positionTracker.GetKomaIdBelongTo();

            KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
            if (komaComponent == null)
            {
                ApplicationLog.LogWarning(nameof(PageComponent), ZString.Format("KomaComponent is not found. {0}", komaId));
                return;
            }

            TargetTagComponent targetTag = Instantiate(_targetTagPrefab, _tagLayerRectTransform);
            targetTag.Initialize(positionTracker, isBoss);

            targetTag.RectTransform.anchoredPosition = targetTag.FieldViewPositionTracker.GetUIPosition(
                komaId,
                komaComponent.BattleFieldRawImageRectTransform,
                _tagLayerRectTransform,
                Camera.main);

            _targetTagComponents.Add(targetTag);
        }

        public void AddUnitConditionComponent(
            FieldViewPositionTracker positionTracker,
            FieldObjectId fieldObjectId,
            BattleSide battleSide,
            CharacterUnitKind kind,
            MasterDataId characterId)
        {
            KomaId komaId = positionTracker.GetKomaIdBelongTo();

            KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
            if (komaComponent == null)
            {
                ApplicationLog.LogWarning(nameof(PageComponent), ZString.Format("KomaComponent is not found. {0}", komaId));
                return;
            }

            FieldUnitConditionComponent conditionComponent = InstantiateConditionComponent(battleSide, kind);

            conditionComponent.FieldViewPositionTracker = positionTracker;
            conditionComponent.SetupHpGauge(komaComponent.IsDarknessCleared());
            conditionComponent.CharacterId = characterId;

            conditionComponent.RectTransform.anchoredPosition = conditionComponent.FieldViewPositionTracker.GetUIPosition(
                komaId,
                komaComponent.BattleFieldRawImageRectTransform,
                _hpGaugeLayerRectTransform,
                Camera.main);

            _fieldUnitConditionComponentDictionary.Add(fieldObjectId, conditionComponent);
        }

        public void SetUnitConditionVisible(bool isVisible)
        {
            _hpGaugeLayerRectTransform.gameObject.SetActive(isVisible);
        }

        public AbstractMangaEffectComponent GenerateMangaEffect(GameObject prefab, FieldViewCoordV2 pos, bool isFlip)
        {
            return _mangaEffectLayer.Generate(prefab, pos, isFlip);
        }
        
        public DamageNumberDisplayComponent GenerateDamageNumberEffect(
            FieldViewCoordV2 pos,
            FieldViewCoordV2 offsetPos,
            AppliedAttackResultModel appliedAttackResultModel)
        {
            return _damageDisplayLayer.Generate(pos, offsetPos, appliedAttackResultModel);
        }

        public T GenerateMangaEffect<T>(T prefab, FieldViewCoordV2 pos, bool isFlip) where T : AbstractMangaEffectComponent
        {
            return _mangaEffectLayer.Generate(prefab, pos, isFlip);
        }

        public T GenerateMangaEffect<T>(
            T prefab,
            IFieldViewPagePositionTrackerTarget trackingTarget,
            bool isFlip) where T : AbstractMangaEffectComponent
        {
            return _mangaEffectLayer.Generate(prefab, trackingTarget, isFlip);
        }

        public UIEffectComponent GenerateEffect(PageEffectId id, FieldViewCoordV2 pos)
        {
            return _effectLayer.Generate(id, pos);
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            _mangaEffectLayer.PauseAllEffects(handler);
            _effectLayer.PauseAllEffects(handler);
            _damageDisplayLayer.PauseAllEffects(handler);

            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.Pause(handler);
            }

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutDarknessClear(MultipleSwitchHandler handler)
        {
            _mangaEffectLayer.PauseAllEffects(handler);
            _effectLayer.PauseAllEffects(handler);
            _damageDisplayLayer.PauseAllEffects(handler);

            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.PauseWithoutDarknessClear(handler);
            }

            return handler;
        }

        public void UpdateAttackViews(IReadOnlyList<IAttackModel> attackModels)
        {
        }

        /// <summary> スペシャルユニット配置コマ選択開始時の選択可能コマ設定 </summary>
        public void StartSpecialUnitKomaSelection(
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> summoningKomaIds)
        {
            int count = 0;
            foreach (KomaComponent komaComponent in _komaComponents)
            {
                var isSelectable = CanSelectAsSpecialUnitSummonTarget(
                    count,
                    komaComponent.KomaId,
                    komaRange,
                    komaDictionary,
                    summoningKomaIds);
                komaComponent.StartKomaSelection(isSelectable);

                count++;
            }
        }

        public void EndKomaSelection()
        {
            foreach (KomaComponent komaComponent in _komaComponents)
            {
                komaComponent.EndKomaSelection();
            }
        }

        /// <summary> スペシャルユニット選択中用の選択可能コマ更新 </summary>
        public void UpdateKomaSelectable(
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> specialUnitKomaIds)
        {
            int count = 0;
            foreach (KomaComponent komaComponent in _komaComponents)
            {
                var isSelectable = CanSelectAsSpecialUnitSummonTarget(
                    count,
                    komaComponent.KomaId,
                    komaRange,
                    komaDictionary,
                    specialUnitKomaIds);
                komaComponent.SetKomaSelectable(isSelectable);

                count++;
            }
        }

        /// <summary> スペシャルユニットの必殺技発動中に表示される、効果範囲内コマの強調表示 </summary>
        public void ShowHighlightKomaWithinSpecialCoordinateRange(CoordinateRange fieldViewCoordinateRange)
        {
            foreach (var komaComponent in _komaComponents)
            {
                var isIntersect = CoordinateRange.IsIntersect(fieldViewCoordinateRange, komaComponent.KomaRangeOnFieldViewCoord);
                komaComponent.ShowSpecialUnitSpecialAttackInEffectRange(isIntersect);
            }
        }

        /// <summary> スペシャルユニットの必殺技発動中の強調表示から通常に戻す </summary>
        public void HideKomaHighlightWithinSpecialCoordinateRange()
        {
            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.HideSpecialUnitSpecialAttackInEffectRange();
            }
        }

        /// <summary> 該当コマがスペシャルユニット配置可能かの判定。射程範囲内、別のスペシャルユニットが居ないか、コマの特性 </summary>
        bool CanSelectAsSpecialUnitSummonTarget(
            int komaIndex,
            KomaId komaId,
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> summoningKomaIds)
        {
            if (!komaRange.IsInRange(komaIndex, BattleSide.Player)) return false;
            if (!komaDictionary.ContainsKey(komaId)) return false;
            if (summoningKomaIds.Contains(komaId)) return false;

            var komaModel = komaDictionary[komaId];
            return komaModel.CanSelectAsSpecialUnitSummonTarget();
        }

        public void Shake(float duration)
        {
            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.Shake(duration);
            }
        }

        public void ShakeKomaAt(FieldViewCoordV2 pos, float duration)
        {
            var komaComponent = GetKomaComponentAt(pos);
            if (komaComponent != null)
            {
                komaComponent.Shake(duration);
            }
        }

        public MultipleSwitchHandler StartShake()
        {
            var handler = new MultipleSwitchHandler();

            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.StartShake(handler);
            }

            return handler;
        }

        public async UniTask ScalePageTo(float scale, float duration, CancellationToken cancellationToken)
        {
            await _pageScaringRootTransform.DOScale(scale, duration).WithCancellation(cancellationToken);
        }

        public async UniTask ScalePageTo(FieldViewCoordV2 pos, float scale, float duration, CancellationToken cancellationToken)
        {
            var pivot = GetPageScalingPivot(pos);
            _pageScaringRootTransform.pivot = pivot;

            await _pageScaringRootTransform.DOScale(scale, duration).WithCancellation(cancellationToken);
        }

        public void ScalePageTo(FieldViewCoordV2 pos, float scale)
        {
            var pivot = GetPageScalingPivot(pos);
            _pageScaringRootTransform.pivot = pivot;

            _pageScaringRootTransform.localScale = new Vector3(scale, scale, 1f);
        }

        Vector2 GetPageScalingPivot(FieldViewCoordV2 pos)
        {
            var fieldCoordPos = _viewCoordinateConverter.ToFieldCoord(pos);
            var pageCoordPos = _coordinateConverter.FieldToPageCoord(fieldCoordPos);

            var pivotX = pageCoordPos.X;

            // あまり端すぎるといろいろ見切れるので、端付近を中心に拡大する場合は、いっそのこと端を中心にしてしまう
            if (pivotX < 0.2f)
            {
                pivotX = 0f;
            }
            if (pivotX > 0.8f)
            {
                pivotX = 1f;
            }

            var pivotY = pageCoordPos.Y * _pageWidth / _pageHeight;

            return new Vector2(1f - pivotX, 1f - pivotY);
        }

        public void ResetPageScale()
        {
            _pageScaringRootTransform.localScale = Vector3.one;
        }

        public void StartTracking(FieldViewPositionTracker tracker)
        {
            KomaId komaId = tracker.GetKomaIdBelongTo();

            KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
            if (komaComponent == null) return;

            komaComponent.StartTracking(tracker);
        }

        public void EndTracking()
        {
            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.EndTracking();
            }
        }

        public MultipleSwitchHandler ExpandKomaSetFieldImage(FieldViewCoordV2 targetPos)
        {
            var komaSetComponent = GetKomaSetComponentAt(targetPos);
            if (komaSetComponent == null) return new MultipleSwitchHandler();

            return komaSetComponent.ExpandFieldImage();
        }

        /// <returns>スクロール量</returns>
        public async UniTask<float> ScrollTo(
            FieldViewCoordV2 pos,
            float maxDuration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false,
            bool isUnrestricted = false)
        {
            if (!_isScrollablePage) return 0f;

            var targetScrollPos = GetScrollPosition(pos, isUnrestricted);
            var scrollLength = Mathf.Abs(targetScrollPos - _scrollRect.verticalNormalizedPosition);
            var duration = scrollLength * maxDuration;

            await DOTween.To(
                    () => _scrollRect.verticalNormalizedPosition,
                    value => _scrollRect.verticalNormalizedPosition = value,
                    targetScrollPos,
                    duration)
                .SetEase(Ease.InOutQuad)
                .SetUpdate(isIndependentUpdate)
                .WithCancellation(cancellationToken);

            return scrollLength;
        }

        public async UniTask<float> ScrollTo(
            float targetNormalizedPosY,
            float duration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false)
        {
            if (!_isScrollablePage) return 0f;

            var scrollLength = Mathf.Abs(targetNormalizedPosY - _scrollRect.verticalNormalizedPosition);
            await DOTween.To(
                    () => _scrollRect.verticalNormalizedPosition,
                    value => _scrollRect.verticalNormalizedPosition = value,
                    targetNormalizedPosY,
                    duration)
                .SetEase(Ease.InOutQuad)
                .SetUpdate(isIndependentUpdate)
                .WithCancellation(cancellationToken);

            return scrollLength;
        }

        public float GetCurrentScrollPosition()
        {
            return _scrollRect.verticalNormalizedPosition;
        }

        public float GetScrollPosition(FieldViewCoordV2 targetPos, bool isUnrestricted)
        {
            var komaSetComponent = GetKomaSetComponentAt(targetPos);
            if (komaSetComponent == null) return 0f;

            var diffFromPageCenter = komaSetComponent.CenterPos.y - _pageHeight * -0.5f;
            var normalizeDiff = ToNormalizedScrollPos(diffFromPageCenter);

            return isUnrestricted
                ? 0.5f - normalizeDiff
                : Mathf.Clamp(0.5f - normalizeDiff, 0f, 1f);
        }

        public void SetScrollPosition(float pos)
        {
            if (!_isScrollablePage) return;

            _scrollRect.verticalNormalizedPosition = pos;
        }

        public PageCoordV2 GetDisplayingPageCenter()
        {
            if (!_isScrollablePage || _pageHeight == 0f)
            {
                var y = ToPageCoordPos(_pageHeight * 0.5f);
                return new PageCoordV2(0.5f, y);
            }

            var pageCoordHeight = ToPageCoordPos(_pageHeight);
            var displayingPageCoordHeight = _scrollRectHeight / _pageHeight * pageCoordHeight;
            var diffPageCoordHeight = pageCoordHeight - displayingPageCoordHeight;

            var centerPageCoordY = ToPageCoordPos(_pageHeight * 0.5f);
            var diffPageCoordYFromCenter = -(_scrollRect.verticalNormalizedPosition - 0.5f) * diffPageCoordHeight;
            var displayingCenterPageCoordY = centerPageCoordY + diffPageCoordYFromCenter;

            return new PageCoordV2(0.5f, displayingCenterPageCoordY);
        }

        public Vector2 GetKomaSetCenterPos(FieldViewCoordV2 pos)
        {
            var komaSetComponent = GetKomaSetComponentAt(pos);
            return komaSetComponent != null ? komaSetComponent.CenterPos : Vector2.zero;
        }

        public void SetUnrestrictedScroll()
        {
            _scrollRect.movementType = ScrollRect.MovementType.Unrestricted;
        }

        public void SetElasticScroll()
        {
            _scrollRect.movementType = ScrollRect.MovementType.Elastic;
        }

        public bool IsDarknessKomaCleared(FieldViewCoordV2 position)
        {
            var komaComponent = GetKomaComponentAt(position);
            if (komaComponent == null) return true;

            return komaComponent.IsDarknessCleared();
        }

        public KomaId GetKomaId(FieldViewCoordV2 targetPosition)
        {
            var komaComponent = GetKomaComponentAt(targetPosition);
            if (komaComponent == null) return KomaId.Empty;

            return komaComponent.KomaId;
        }

        public FieldUnitConditionComponent GetFieldUnitConditionComponent(FieldObjectId fieldObjectId)
        {
            _fieldUnitConditionComponentDictionary.TryGetValue(fieldObjectId, out var component);
            if (component == null)
            {
                ApplicationLog.LogWarning(
                    nameof(GetFieldUnitConditionComponent),
                    ZString.Format("ConditionComponent is not found. {0}", fieldObjectId.Value));
            }
            return component;
        }
        
        public void SetDamageDisplayVisible(DamageDisplayFlag isVisible)
        {
            _damageDisplayLayer.SetAllActiveEffectsVisible(isVisible);
        }

        void InitializeKoma(MstPageModel mstPage, BattleFieldView battleFieldView, out float totalHeight)
        {
            _komaSetComponents.Clear();
            _komaComponents.Clear();

            float posY = 0f;
            float fieldCoordPosX = 0f;

            for (int i = 0; i < mstPage.KomaLineList.Count; i++)
            {
                var line = mstPage.KomaLineList[i];

                float height = line.Height * _pageWidth;
                bool isTop = i == 0;
                bool isBottom = i == mstPage.KomaLineList.Count - 1;

                KomaSetComponent komaSetComponent = CreateKomaSetComponent(line.KomaSetTypeAssetPath, posY, height);
                if (komaSetComponent.KomaComponents.Count != line.KomaList.Count)
                {
                    throw new Exception("a number of koma is not matched.");
                }

                var pairs = komaSetComponent.KomaComponents
                    .Zip(line.KomaList, (component, model) => new { Component = component, Model = model })
                    .ToList();

                for (int j = 0; j < pairs.Count(); j++)
                {
                    var pair = pairs[j];

                    bool isRight = j == 0;
                    bool isLeft = j == pairs.Count - 1;

                    FieldCoordV2 fieldCoordKomaRight = new FieldCoordV2(fieldCoordPosX, 0);
                    FieldCoordV2 fieldCoordKomaLeft = new FieldCoordV2(fieldCoordPosX + pair.Model.Width, 0);

                    FieldViewCoordV2 fieldViewCoordKomaRight = _viewCoordinateConverter.ToFieldViewCoord(fieldCoordKomaRight);
                    FieldViewCoordV2 fieldViewCoordKomaLeft = _viewCoordinateConverter.ToFieldViewCoord(fieldCoordKomaLeft);

                    CoordinateRange komaRange =
                        CoordinateRange.BetweenPoints(fieldViewCoordKomaRight.X, fieldViewCoordKomaLeft.X);

                    KomaComponent.KomaLayout layout = GetKomaLayout(isTop, isBottom, isRight, isLeft);

                    InitializeKomaComponent(pair.Component, pair.Model, komaSetComponent, battleFieldView, komaRange, layout);

                    fieldCoordPosX += pair.Model.Width;
                }

                _komaSetComponents.Add(komaSetComponent);
                _komaComponents.AddRange(komaSetComponent.KomaComponents);

                posY -= komaSetComponent.RectTransform.sizeDelta.y;
            }

            totalHeight = -posY;
        }

        KomaComponent.KomaLayout GetKomaLayout(bool isTop, bool isBottom, bool isRight, bool isLeft)
        {
            if (isTop)
            {
                if (isRight && isLeft) return KomaComponent.KomaLayout.WholeTop;
                if (isRight) return KomaComponent.KomaLayout.RightTop;
                if (isLeft) return KomaComponent.KomaLayout.LeftTop;
                return KomaComponent.KomaLayout.MiddleTop;
            }

            if (isBottom)
            {
                if (isRight && isLeft) return KomaComponent.KomaLayout.WholeBottom;
                if (isRight) return KomaComponent.KomaLayout.RightBottom;
                if (isLeft) return KomaComponent.KomaLayout.LeftBottom;
                return KomaComponent.KomaLayout.MiddleBottom;
            }

            if (isRight && isLeft) return KomaComponent.KomaLayout.WholeMiddle;
            if (isRight) return KomaComponent.KomaLayout.RightMiddle;
            if (isLeft) return KomaComponent.KomaLayout.LeftMiddle;

            return KomaComponent.KomaLayout.Middle;
        }

        KomaSetComponent CreateKomaSetComponent(KomaSetTypeAssetPath assetPath, float posY, float height)
        {
            KomaSetComponent komaSetPrefab = GetKomaSetPrefab(assetPath);
            var komaSetComponent = _komaSetComponentFactory.Create(komaSetPrefab);

            komaSetComponent.transform.SetParent(_komaLayerRectTransform, false);

            var rectTransform = komaSetComponent.RectTransform;

            rectTransform.anchorMin = new Vector2(0.5f, 1f);
            rectTransform.anchorMax = new Vector2(0.5f, 1f);
            rectTransform.pivot = new Vector2(0.5f, 1f);

            rectTransform.anchoredPosition = new Vector2(0, posY);
            rectTransform.sizeDelta = new Vector2(rectTransform.sizeDelta.x, height);

            return komaSetComponent;
        }

        void InitializeKomaComponent(
            KomaComponent komaComponent,
            MstKomaModel mstKomaModel,
            KomaSetComponent parentKomaSetComponent,
            BattleFieldView battleFieldView,
            CoordinateRange komaRange,
            KomaComponent.KomaLayout layout)
        {
            Rect komaUVRect = battleFieldView.GetKomaUVRect(mstKomaModel.KomaId);
            Sprite komaBackground = GetKomaBackground(mstKomaModel.BackgroundAssetKey);

            komaComponent.InitializeKoma(
                mstKomaModel.KomaId,
                parentKomaSetComponent,
                layout,
                komaBackground,
                mstKomaModel.BackgroundOffset,
                mstKomaModel.KomaEffectType,
                battleFieldView.BattleFieldRenderTexture,
                komaUVRect,
                komaRange);
        }

        KomaSetComponent GetKomaSetPrefab(KomaSetTypeAssetPath assetPath)
        {
            // int index = assetPath.Value - 1;
            // if (index < 0 || index >= _komaSetPrefabs.Count)
            // {
            //     ApplicationLog.LogWarning(nameof(PageComponent),
            //         ZString.Format("KomaSetTypeId is out of range. {0}", assetPath));
            //     return null;
            // }

            var result = _komaSetPrefabComponents.FirstOrDefault(p => p.AssetPath == assetPath.Value);
            if (result == null)
            {
                ApplicationLog.LogWarning(nameof(PageComponent),
                    ZString.Format("KomaSetTypeId is out of range. {0}", assetPath));
                return null;
            }
            else return result.Component;
        }

        Sprite GetKomaBackground(KomaBackgroundAssetKey assetKey)
        {
            return _komaBackgroundContainer.Get(assetKey);
        }

        void Update()
        {
            UpdateBossTags();
            UpdateFieldUnitCondition();
            UpdateTargetTags();
        }

        void UpdateBossTags()
        {
            DeleteDiscardedBossTags();
            UpdateBossTagsPosition();
        }

        void DeleteDiscardedBossTags()
        {
            var discardedBossTags = _bossTagComponents
                .FindAll(bossTag => bossTag.FieldViewPositionTracker.IsTargetDestroyed());

            _bossTagComponents = _bossTagComponents.Except(discardedBossTags).ToList();

            foreach (BossTagComponent discardedBossTag in discardedBossTags)
            {
                Destroy(discardedBossTag.gameObject);
            }
        }

        void UpdateBossTagsPosition()
        {
            foreach (BossTagComponent bossTag in _bossTagComponents)
            {
                KomaId komaId = bossTag.FieldViewPositionTracker.GetKomaIdBelongTo();

                KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
                if (komaComponent == null) continue;

                bossTag.RectTransform.anchoredPosition = bossTag.FieldViewPositionTracker.GetUIPosition(
                    komaId,
                    komaComponent.BattleFieldRawImageRectTransform,
                    _tagLayerRectTransform,
                    Camera.main);
            }
        }

        void UpdateTargetTags()
        {
            DeleteDiscardedTargetTags();
            UpdateTargetTagsPosition();
        }

        void DeleteDiscardedTargetTags()
        {
            var discardedTargetTags = _targetTagComponents
                .FindAll(targetTag => targetTag.FieldViewPositionTracker.IsTargetDestroyed());

            _targetTagComponents = _targetTagComponents.Except(discardedTargetTags).ToList();

            foreach (TargetTagComponent discardedTargetTag in discardedTargetTags)
            {
                Destroy(discardedTargetTag.gameObject);
            }
        }

        void UpdateTargetTagsPosition()
        {
            foreach (TargetTagComponent targetTag in _targetTagComponents)
            {
                KomaId komaId = targetTag.FieldViewPositionTracker.GetKomaIdBelongTo();

                KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
                if (komaComponent == null) continue;

                targetTag.RectTransform.anchoredPosition = targetTag.FieldViewPositionTracker.GetUIPosition(
                    komaId,
                    komaComponent.BattleFieldRawImageRectTransform,
                    _tagLayerRectTransform,
                    Camera.main);
            }
        }

        void UpdateFieldUnitCondition()
        {
            DeleteDiscardedUnitConditionComponent();
            UpdateUnitConditionComponentPosition();
        }

        void DeleteDiscardedUnitConditionComponent()
        {
            var discardedUnitConditionComponent = _fieldUnitConditionComponentDictionary
                .Where(pair => pair.Value.FieldViewPositionTracker.IsTargetDestroyed())
                .ToList();

            _fieldUnitConditionComponentDictionary = _fieldUnitConditionComponentDictionary
                .Except(discardedUnitConditionComponent).ToDictionary(pair => pair.Key, pair => pair.Value);

            foreach (var pair in discardedUnitConditionComponent)
            {
                Destroy(pair.Value.gameObject);
            }
        }

        void UpdateUnitConditionComponentPosition()
        {
            foreach (var pair in _fieldUnitConditionComponentDictionary)
            {
                KomaId komaId = pair.Value.FieldViewPositionTracker.GetKomaIdBelongTo();

                KomaComponent komaComponent = _komaComponents.Find(koma => koma.KomaId == komaId);
                if (komaComponent == null) continue;

                pair.Value.RectTransform.anchoredPosition = pair.Value.FieldViewPositionTracker.GetUIPosition(
                    komaId,
                    komaComponent.BattleFieldRawImageRectTransform,
                    _tagLayerRectTransform,
                    Camera.main);
            }
        }

        void OnTouchLayerTouched(Vector2 position)
        {
            var pageCoordPos = new PageCoordV2(-position.x / _pageWidth, -position.y / _pageWidth);
            OnTouch?.Invoke(pageCoordPos);
        }

        KomaComponent GetKomaComponentAt(FieldViewCoordV2 pos)
        {
            return _komaComponents.Find(koma => koma.ContainsAt(pos));
        }

        KomaSetComponent GetKomaSetComponentAt(FieldViewCoordV2 pos)
        {
            return _komaSetComponents.Find(komaSet => komaSet.KomaComponents.Any(koma => koma.ContainsAt(pos)));
        }

        float ToNormalizedScrollPos(float pos)
        {
            var diff = _scrollRectHeight - _pageHeight;
            return diff != 0f ? pos / diff : 0f;
        }

        FieldUnitConditionComponent InstantiateConditionComponent(BattleSide battleSide, CharacterUnitKind kind)
        {
            if(battleSide == BattleSide.Player)
            {
                return Instantiate(_fieldUnitConditionPlayer, _hpGaugeLayerRectTransform);
            }

            if (kind == CharacterUnitKind.AdventBattleBoss)
            {
                return Instantiate(_fieldUnitConditionAdventBoss, _hpGaugeLayerRectTransform);
            }

            return Instantiate(_fieldUnitConditionEnemy, _hpGaugeLayerRectTransform);
        }

        float ToPageCoordPos(float uiPos)
        {
            return _pageWidth != 0f ? uiPos / _pageWidth : 0f;
        }
    }
}
