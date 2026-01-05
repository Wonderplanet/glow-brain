using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine.Unity;
using UnityEngine;
using WonderPlanet.RandomGenerator;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class FieldUnitView : MonoBehaviour, IFieldViewPagePositionTrackerTarget, IKomaShakeTrackClipDelegate
    {
        [SerializeField] Transform _characterImageRoot;
        [SerializeField] DamageOnomatopoeiaComponent _damageOnomatopoeiaPrefab;
        [SerializeField] FieldUnitShadowTrace _shadowObj;

        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] BattleEffectManager BattleEffectManager { get; }
        [Inject] BattleStateEffectViewManager BattleStateEffectViewManager { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }
        [Inject] IRandomizer Randomizer { get; }

        IScreenFlashTrackClipDelegate _screenFlashTrackClipDelegate;

        PageComponent _pageComponent;
        CharacterColor _unitColor;
        CharacterUnitRoleType _unitRoleType;

        Vector3 _marchingLanePos;

        UnitImage _unitImage;
        UnitTagPosition _unitTagPosition;
        IFieldViewPositionTrackerTarget _unitTrackingPosition;
        UnitConditionPosition _fieldUnitConditionComponentPosition;
        readonly FieldUnitViewKnockBackController _knockBackController = new();
        Tween _tween;
        UnitAttackViewInfoSet _unitAttackViewInfoSet;
        BaseBattleEffectView _specialAttackReadyEffect;
        BaseBattleEffectView _specialAttackAuraEffect;
        BaseBattleEffectView _attackEffect;
        BaseBattleEffectView _attackStayedLastingEffect;
        BaseBattleEffectView _aura;
        BaseBattleEffectView _stunEffect;
        FreezeEffectView _freezeEffect;
        BaseBattleEffectView _weakeningEffect;
        AbstractMangaEffectComponent _attackMangaEffect;
        IReadOnlyList<IStateEffectModel> _stateEffects = new List<IStateEffectModel>();
        bool _isInterruptSlide;
        MultipleSwitchHandler _interruptSlidePauseHandler;
        readonly MultipleSwitchController _pauseController = new ();
        Dictionary<UnitAnimationType, CharacterUnitAnimation> _animationDictionary = new ();

        readonly HitStopController _hitStopController = new();
        MultipleSwitchHandler _hitStopPauseHandler;

        bool _isHpGaugeUpdateStopping;

        public FieldObjectId Id { get; private set; }
        public MasterDataId CharacterId { get; private set; }
        public BattleSide BattleSide { get; private set; }
        public CharacterColor UnitColor => _unitColor;
        public AutoPlayerSequenceElementId AutoPlayerSequenceElementId { get; private set; }
        public UnitImage UnitImage => _unitImage;
        public UnitTagPosition TagPosition => _unitTagPosition;
        public IFieldViewPositionTrackerTarget TrackingPosition => _unitTrackingPosition;
        public UnitConditionPosition ConditionComponentPosition => _fieldUnitConditionComponentPosition;
        public Transform EffectRoot => _unitImage.EffectRoot;
        public SkeletonAnimation SkeletonAnimation => _unitImage.SkeletonAnimation;
        public MarchingLaneIdentifier MarchingLane { get; set; } = MarchingLaneIdentifier.Empty;

        public FieldViewCoordV2 FieldViewPos
        {
            get
            {
                var pos = transform.localPosition;
                return new FieldViewCoordV2(pos.x, pos.y);
            }
        }

        public FieldViewCoordV2 TagFieldViewPos
        {
            get
            {
                var pos = _unitImage.TagPosition.position - transform.position + transform.localPosition;
                return new FieldViewCoordV2(pos.x, pos.y);
            }
        }

        public FieldViewCoordV2 TrackingFieldViewPos
        {
            get
            {
                var localPos = _unitTrackingPosition.GetWorldPos() - transform.position.ToVector2();
                var pos = transform.localPosition.ToVector2() + localPos;

                return new FieldViewCoordV2(pos.x, pos.y);
            }
        }

        public bool UnitVisible
        {
            get => _unitImage.gameObject.activeSelf;
            set => _unitImage.gameObject.SetActive(value);
        }

        void Awake()
        {
            _pauseController.OnStateChanged = OnPause;

            _hitStopController.OnHitStopStarted = OnHitStopStarted;
            _hitStopController.OnHitStopEnded = OnHitStopEnded;
        }

        void OnDestroy()
        {
            _knockBackController.Dispose();
            _hitStopController.Dispose();

            _interruptSlidePauseHandler?.Dispose();
            _hitStopPauseHandler?.Dispose();

            _pauseController.Dispose();
            _shadowObj.Clear();

            if (_attackStayedLastingEffect != null)
            {
                _attackStayedLastingEffect.Destroy();
            }
        }

        public void InitializeCharacterUnitView(
            CharacterUnitModel characterUnitModel,
            UnitImage unitImage,
            int sortingOrder,
            IViewCoordinateConverter viewCoordinateConverter,
            PageComponent pageComponent,
            IScreenFlashTrackClipDelegate screenFlashTrackClipDelegate)
        {
            _pageComponent = pageComponent;
            _screenFlashTrackClipDelegate = screenFlashTrackClipDelegate;

            Id = characterUnitModel.Id;
            CharacterId = characterUnitModel.CharacterId;
            BattleSide = characterUnitModel.BattleSide;
            AutoPlayerSequenceElementId = characterUnitModel.AutoPlayerSequenceElementId;
            MarchingLane = characterUnitModel.MarchingLane;

            _unitColor = characterUnitModel.Color;
            _unitRoleType = characterUnitModel.RoleType;

            bool isFlip = BattleSide == BattleSide.Enemy;

            _unitImage = unitImage;
            _unitImage.SortingOrder = sortingOrder;
            _unitImage.Flip = isFlip;
            _unitImage.SetUnitColor(CharacterColor.Colorless);
            _unitImage.SetPhantomized(characterUnitModel.Phantomized);

            var characterImageTransform = _unitImage.transform;
            characterImageTransform.parent = _characterImageRoot;
            characterImageTransform.localPosition = Vector3.zero;
            characterImageTransform.localScale = Vector3.one;
            _shadowObj.RegisterSkeletonAnimation(SkeletonAnimation);
            _shadowObj.SetupShadowColor(characterUnitModel.Color);

            _animationDictionary = CreateUnitAnimationDictionary(isFlip);
            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);

            SetPos(characterUnitModel, viewCoordinateConverter);

            var myTransform = transform;
            var scale = myTransform.localScale;
            myTransform.localScale = new Vector3(scale.x, scale.y, scale.z * 0.1f);

            GenerateAura(characterUnitModel);

            _unitTagPosition = new UnitTagPosition(_unitImage);
            _fieldUnitConditionComponentPosition = new UnitConditionPosition(_unitImage);
            _unitTrackingPosition = new UnitTrackingPosition(_unitImage);

            _unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(characterUnitModel.AssetKey);
        }

        public void UpdateCharacterUnitView(CharacterUnitModel characterUnitModel,
            IViewCoordinateConverter viewCoordinateConverter)
        {
            SetPos(characterUnitModel, viewCoordinateConverter);
        }

        public void OnRestart()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);
        }


        public void OnStartMove()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            if (CanStartMoveAnimation())
            {
                _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Move], CharacterUnitAnimation.Empty);
            }
            else
            {
                _unitImage.SetNextAnimation(_animationDictionary[UnitAnimationType.Move]);
            }
        }

        public void OnStartKnockBack(TickCount duration)
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _knockBackController.StartKnockBack(_unitImage, duration);
        }

        public void OnStartSpecialAttackCharge()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.SpecialAttackCharge], CharacterUnitAnimation.Empty);

            ShowAttackAuraEffect();
        }

        public void OnWait()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);
        }

        public void OnAttack(AttackData attackData)
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Attack], CharacterUnitAnimation.Empty);

            if (_unitAttackViewInfoSet != null)
            {
                GenerateAttackEffect(_unitAttackViewInfoSet.NormalAttackViewInfo);
            }
        }

        public void OnAppearanceAttack(AttackData attackData)
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            var mainAttackElement = attackData.MainAttackElement;

            var appearanceAnimation = mainAttackElement.AttackDamageType == AttackDamageType.None
                ? _animationDictionary[UnitAnimationType.Wait]
                : _animationDictionary[UnitAnimationType.Attack];

            _unitImage.StartAnimation(appearanceAnimation, _animationDictionary[UnitAnimationType.Wait]);
        }

        public void OnSpecialAttack(AttackData attackData)
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            ResetForSpecialAttack();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.SpecialAttack], CharacterUnitAnimation.Empty);

            if (_unitAttackViewInfoSet != null)
            {
                GenerateAttackEffect(_unitAttackViewInfoSet.SpecialAttackViewInfo);
            }
        }

        public void OnStun()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Stun], CharacterUnitAnimation.Empty);
        }

        public void OnFreeze()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Freeze], CharacterUnitAnimation.Empty);
        }

        public void OnHitAttacks(
            CharacterUnitModel unitModel,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels,
            DamageInvalidationFlag isDamageInvalidation,
            DamageDisplayFlag isDamageDisplay)
        {
            // ヒットエフェクト
            GenerateHitEffects(appliedAttackResultModels);

            // ヒット擬音
            GenerateAttackHitOnomatopoeia(appliedAttackResultModels);

            // ヒットSE
            PlayAttackHitSe(unitModel, appliedAttackResultModels);
            
            if (isDamageDisplay)
            {
                PlayDamageNumberAnimation(appliedAttackResultModels);
            }

            // ダメージモーション
            if (!_isInterruptSlide && !isDamageInvalidation)
            {
                PlayAttackHitAnimation(appliedAttackResultModels);
            }

            // 毒ダメージ演出
            if (appliedAttackResultModels.Any(res => res.AttackDamageType == AttackDamageType.PoisonDamage))
            {
                DamagePoison();
            }

            // 火傷ダメージ演出
            if (appliedAttackResultModels.Any(res => res.AttackDamageType == AttackDamageType.BurnDamage))
            {
                DamageBurn();
            }
        }

        public void StartHitStop()
        {
            _hitStopController.StartHitStop();
        }

        public async UniTask OnDead(bool isAnimation, UnitDeathType deathType, CancellationToken cancellationToken)
        {
            Reset();
            HideSpecialAttackReadyEffect();

            if (_aura != null)
            {
                _aura.Destroy();
                _aura = null;
            }

            if (_stunEffect != null)
            {
                _stunEffect.Destroy();
                _stunEffect = null;
            }

            if (_freezeEffect != null)
            {
                _freezeEffect.Destroy();
                _freezeEffect = null;
            }

            if (_weakeningEffect != null)
            {
                _weakeningEffect.Destroy();
                _weakeningEffect = null;
            }

            if (isAnimation)
            {
                if (deathType == UnitDeathType.Escape)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_030);
                }
                else
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_065);
                }

                await PlayDeathMotion(deathType, cancellationToken);

                // 撃破エフェクトの再生
                PlayDeathEffect(deathType);
            }
        }

        public void UpdateStateEffects(IReadOnlyList<IStateEffectModel> stateEffects)
        {
            GenerateStateEffectEffectAndPlaySe(_stateEffects, stateEffects);
            _stateEffects = stateEffects;

            var stateEffectTypeList = stateEffects
                .Where(stateEffect => stateEffect.NeedsDisplay)
                .Select(stateEffect => stateEffect.Type)
                .ToList();

            var conditionComponent = _pageComponent.GetFieldUnitConditionComponent(Id);
            if (conditionComponent != null)
            {
                conditionComponent.UpdateStateEffects(stateEffectTypeList, BattleStateEffectViewManager);
            }
        }

        public MultipleSwitchHandler PauseAnimation(MultipleSwitchHandler handler)
        {
            _pauseController.TurnOn(handler);
            _unitImage.PauseAnimation(handler);

            return handler;
        }

        public void OnEffectBlocked()
        {
            BattleEffectManager
                .Generate(BattleEffectId.StateBlock, transform)
                ?.BindCharacterUnit(this)
                ?.Play();

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_010);
        }

        public void OnSurvivedByGuts()
        {
            BattleEffectManager
                .Generate(BattleEffectId.Guts, transform)
                ?.BindCharacterUnit(this)
                ?.Play();

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_033);
        }

        public BaseBattleEffectView OnRushAttackPowerUp()
        {
            Vector3 setPos = ((IFieldViewPositionTrackerTarget)TagPosition).GetWorldPos();
            setPos.z = transform.position.z;
            var effectView = BattleEffectManager
                .Generate(BattleEffectId.RushAttackPowerUp, setPos)
                ?.BindCharacterUnit(this)
                ?.Play();

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_034);

            return effectView;
        }

        public BaseBattleEffectView PlayTagPositionEffect(BattleEffectId battleEffectId)
        {
            if (battleEffectId == BattleEffectId.None)
            {
                return null;
            }

            var effectView = BattleEffectManager
                .Generate(battleEffectId, transform)
                ?.BindCharacterUnit(this)
                ?.Play();

            return effectView;
        }

        public void OnInterruptSlideStarted()
        {
            _isInterruptSlide = true;

            _interruptSlidePauseHandler?.Dispose();
            _interruptSlidePauseHandler = _pauseController.TurnOn();

            if (_specialAttackAuraEffect != null) _specialAttackAuraEffect.Pause(_interruptSlidePauseHandler);
            if (_attackEffect != null) _attackEffect.Pause(_interruptSlidePauseHandler);
            if (_attackMangaEffect != null) _attackMangaEffect.Pause(_interruptSlidePauseHandler);
        }

        public void OnStartTransformationReady()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
                return;
            }

            Reset();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);
        }

        public FieldViewCoordV2 GetFieldViewCoordPos()
        {
            return FieldViewPos;
        }

        public bool IsDestroyed()
        {
            return this == null;
        }

        public void SetMarchingLanePos(Vector3 marchingLanePos)
        {
            _marchingLanePos = marchingLanePos;

            // 攻撃のエフェクト（キャラに追従しない）も一緒に移動させるため、いったんエフェクトをキャラに付ける
            if (_attackStayedLastingEffect != null)
            {
                _attackStayedLastingEffect.ChangeParent(_unitImage.EffectRoot);
            }

            // 指定された侵攻レーンの位置に移動させる
            var transformTmp = transform;
            transformTmp.localPosition = new Vector3(
                transformTmp.localPosition.x,
                marchingLanePos.y,
                marchingLanePos.z);

            // 攻撃のエフェクト（キャラに追従しない）をキャラから切り離す
            if (_attackStayedLastingEffect)
            {
                _attackStayedLastingEffect.ChangeParent(BattleEffectManager.EffectLayer);
            }
        }

        public void OnGameEnd()
        {
            if (_isInterruptSlide)
            {
                ResetInterruptSlide();
            }

            Reset();
            HideSpecialAttackReadyEffect();

            CharacterUnitAnimation currentAnimation = _unitImage.CurrentAnimation;
            if (currentAnimation.Type is UnitAnimationType.Death or UnitAnimationType.Escape) return;

            if (currentAnimation.IsLoop)
            {
                _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);
            }
            else
            {
                _unitImage.SetNextAnimation(_animationDictionary[UnitAnimationType.Wait]);
            }
        }

        public void ShowAttackAuraEffect()
        {
            if (_specialAttackAuraEffect != null) return;

            _specialAttackAuraEffect = BattleEffectManager
                .Generate(_unitRoleType.GetSpecialAttackAuraEffectId(), _unitImage.EffectRoot)
                ?.BindCharacterUnit(this)
                ?.Play();

            _unitImage.ApplyUnitSizeToSpecifiedBattleEffect(_specialAttackAuraEffect);

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_058);
        }

        public void DestroyAttackAuraEffect()
        {
            if (_specialAttackAuraEffect != null)
            {
                _specialAttackAuraEffect.Destroy();
                _specialAttackAuraEffect = null;

                SoundEffectPlayer.Stop(SoundEffectId.SSE_051_058);
            }
        }

        public void ShowSpecialAttackReadyEffect()
        {
            if (_specialAttackReadyEffect != null) return;

            _specialAttackReadyEffect = BattleEffectManager
                .Generate(BattleEffectId.SpecialAttackReady, _unitImage.EffectRoot)
                ?.Play();
        }

        public void HideSpecialAttackReadyEffect()
        {
            if (_specialAttackReadyEffect == null) return;

            _specialAttackReadyEffect.Destroy();
            _specialAttackReadyEffect = null;
        }

        public void UpdateHp(CharacterUnitModel characterUnitModel)
        {
            var component = _pageComponent.GetFieldUnitConditionComponent(Id);
            if(component == null) return;

            component.UpdateHpGauge(characterUnitModel.MaxHp, characterUnitModel.Hp);
        }

        public void UpdateConditionVisible(CharacterUnitModel characterUnitModel)
        {
            if(_isHpGaugeUpdateStopping) return;

            var component = _pageComponent.GetFieldUnitConditionComponent(Id);
            if(component == null) return;

            var isDarknessKoma = characterUnitModel.LocatedKoma.IsDarknessKoma();
            component.ChangeVisible(!isDarknessKoma);
        }

        public void SetConditionVisible(bool visible)
        {
            _isHpGaugeUpdateStopping = !visible;


            var component = _pageComponent.GetFieldUnitConditionComponent(Id);
            if(component == null) return;

            component.ChangeVisibleImmediately(visible);
        }

        MultipleSwitchHandler IKomaShakeTrackClipDelegate.StartShake()
        {
            return _pageComponent.StartShake();
        }

        void GenerateHitEffects(IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            if (appliedAttackResultModels.Count == 0) return;

            var effectIds = appliedAttackResultModels
                .Where(model =>
                    model.AttackDamageType != AttackDamageType.PoisonDamage &&
                    model.AttackDamageType != AttackDamageType.BurnDamage)
                .Select(GetAttackHitEffect)
                .Distinct()
                .ToList();

            foreach (var effectId in effectIds)
            {
                // エフェクト無し
                if (effectId == BattleEffectId.None) continue;
                
                BattleEffectManager
                    .Generate(effectId, transform)
                    ?.BindCharacterUnit(this)
                    ?.Play();
            }
        }

        BattleEffectId GetAttackHitEffect(AppliedAttackResultModel appliedAttackResultModel)
        {
            var hitBattleEffectId = appliedAttackResultModel.AttackHitData.AttackHitBattleEffectId;

            // ヒットエフェクトが指定されている場合はそのエフェクトを再生
            if (!hitBattleEffectId.IsEmpty())
            {
                return hitBattleEffectId.Value;
            }
            
            // ダメージ無しの場合はヒットエフェクトを表示しない
            if (appliedAttackResultModel.AttackDamageType == AttackDamageType.None)
            {
                return BattleEffectId.None;
            }

            // HP吸収
            if (appliedAttackResultModel.AttackHitData.HitType == AttackHitType.Drain)
            {
                return appliedAttackResultModel.AttackDamageType == AttackDamageType.Heal
                    ? BattleEffectId.DrainHeal
                    : BattleEffectId.DrainDamage;
            }

            // 回復のデフォルト
            if (appliedAttackResultModel.AttackDamageType == AttackDamageType.Heal)
            {
                return BattleEffectId.CommonHeal01;
            }

            // ダメージのデフォルト
            return appliedAttackResultModel.IsKiller ? BattleEffectId.CommonKillerDamage01 : BattleEffectId.CommonHit01;
        }

        void GenerateAttackHitOnomatopoeia(IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            foreach (var appliedAttackResultModel in appliedAttackResultModels)
            {
                var needsOnomatopoeia =
                    !appliedAttackResultModel.AppliedDamage.IsZero() ||
                    !appliedAttackResultModel.AppliedHeal.IsZero();

                if (!needsOnomatopoeia) continue;

                var onomatopoeiaAssetKey = PickAttackHitOnomatopoeia(appliedAttackResultModel.AttackHitData);
                if (onomatopoeiaAssetKey.IsEmpty()) continue;

                _pageComponent
                    .GenerateMangaEffect(_damageOnomatopoeiaPrefab, FieldViewPos, false)
                    ?.Setup(onomatopoeiaAssetKey,
                        appliedAttackResultModel.AttackerColor,
                        appliedAttackResultModel.IsAdvantageColor)
                    ?.Play();
            }
        }

        AttackHitOnomatopoeiaAssetKey PickAttackHitOnomatopoeia(AttackHitData attackHitData)
        {
            var onomatopoeiaAssetKeys = attackHitData.OnomatopoeiaAssetKeys;
            if (onomatopoeiaAssetKeys.Count == 0) return AttackHitOnomatopoeiaAssetKey.Empty;

            return onomatopoeiaAssetKeys[UnityEngine.Random.Range(0, onomatopoeiaAssetKeys.Count)];
        }

        void PlayAttackHitSe(
            CharacterUnitModel unitModel,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            foreach (var appliedAttackResultModel in appliedAttackResultModels)
            {
                PlayAttackHitSe(unitModel, appliedAttackResultModel);
            }
        }

        void PlayAttackHitSe(
            CharacterUnitModel unitModel,
            AppliedAttackResultModel appliedAttackResultModel)
        {
            // 回復SE
            if (appliedAttackResultModel.AttackDamageType == AttackDamageType.Heal)
            {
                if (appliedAttackResultModel.AttackHitData.HitType == AttackHitType.Drain)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_029);  // HP吸収による回復
                }

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_011);  // 通常回復
            }

            // ダメージSE
            if (appliedAttackResultModel.AttackDamageType.IsDamage())
            {
                if (unitModel.Action.ActionState == UnitActionState.Freeze)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_072);  // 凍結中
                }
                else
                {
                    var existsKiller = appliedAttackResultModel.IsKiller;
                    var seAssetKey = existsKiller
                        ? appliedAttackResultModel.AttackHitData.KillerSoundEffectAssetKey
                        : appliedAttackResultModel.AttackHitData.SoundEffectAssetKey;

                    if (seAssetKey.IsEmpty()) return;

                    SoundEffectPlayer.Play(seAssetKey);
                }
            }
        }

        void PlayAttackHitAnimation(IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            if (appliedAttackResultModels.All(res => !res.AttackDamageType.NeedPlayDamageMotion()))
            {
                return;
            }

            CharacterUnitAnimation currentAnimation = _unitImage.CurrentAnimation;
            if (CanDamageAnimationInterrupt(currentAnimation.Type))
            {
                var nextAnimation = currentAnimation.IsLoop ? currentAnimation : _unitImage.NextAnimation;
                _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Damage], nextAnimation);
            }

            _unitImage.Shake(0.2f, 0.1f, 100);
        }
        
        void PlayDamageNumberAnimation(IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            foreach (var appliedAttackResultModel in appliedAttackResultModels)
            {
                var offsetPos = new FieldViewCoordV2(
                    Randomizer.Range(-0.5f, 0.5f),
                    Randomizer.Range(1.0f, 2.0f));
                _pageComponent.GenerateDamageNumberEffect(
                        TrackingFieldViewPos, 
                        offsetPos, 
                        appliedAttackResultModel)
                    ?.Play();
            }
        }

        void DamagePoison()
        {
            Color32 baseColor = _unitImage.Color;
            Color32 afterBaseColor = new Color32(179,51,255,255);
            _unitImage.Color = afterBaseColor;

            DOTween.To(() => _unitImage.Color, x => _unitImage.Color = x, baseColor, 0.5f)
                .SetEase(Ease.Linear)
                .SetDelay(0.5f)
                .Play();

            var effect = BattleEffectManager
                .Generate(BattleEffectId.PoisonDamage, transform)
                .BindCharacterImage(_unitImage);

            effect.AddCompletedAction(() => _unitImage.Color = Color.white);
            effect.Play();
        }

        void DamageBurn()
        {
            Color32 baseColor = _unitImage.Color;
            Color32 afterBaseColor = new Color(1,0.2f,0f,1f);
            _unitImage.Color = afterBaseColor;
            DOTween.To(() => _unitImage.Color, x => _unitImage.Color = x, baseColor, 0.8f)
                .SetEase(Ease.Linear)
                .SetDelay(0.5f)
                .Play();

            var effect = BattleEffectManager
                .Generate(BattleEffectId.BurnDamage, _unitImage.EffectRoot)
                .BindCharacterImage(_unitImage);

            effect.AddCompletedAction(() => _unitImage.Color = Color.white);
            effect.Play();
        }

        void SetPos(CharacterUnitModel characterUnitModel, IViewCoordinateConverter coordinateConverter)
        {
            var pos = coordinateConverter.ToFieldViewCoord(characterUnitModel.BattleSide, characterUnitModel.Pos);
            transform.localPosition = new Vector3(
                pos.X,
                _marchingLanePos.y,
                _marchingLanePos.z);
        }

        void Reset()
        {
            DestroyEffects();
            _knockBackController.Cancel();
        }

        void ResetForSpecialAttack()
        {
            DestroyEffectsForSpecialAttack();
            _knockBackController.Cancel();
        }

        void ResetInterruptSlide()
        {
            _isInterruptSlide = false;

            _interruptSlidePauseHandler?.Dispose();
            _interruptSlidePauseHandler = null;
        }

        void DestroyEffects()
        {
            if (_specialAttackAuraEffect != null)
            {
                _specialAttackAuraEffect.Destroy();
                _specialAttackAuraEffect = null;

                SoundEffectPlayer.Stop(SoundEffectId.SSE_051_058);
            }

            if (_attackEffect != null)
            {
                _attackEffect.Destroy();
                _attackEffect = null;
            }

            if (_attackMangaEffect != null)
            {
                _attackMangaEffect.Destroy();
                _attackMangaEffect = null;
            }
        }

        void DestroyEffectsForSpecialAttack()
        {
            if (_attackEffect != null)
            {
                _attackEffect.Destroy();
                _attackEffect = null;
            }

            if (_attackMangaEffect != null)
            {
                _attackMangaEffect.Destroy();
                _attackMangaEffect = null;
            }
        }

        void GenerateStateEffectEffectAndPlaySe(
            IReadOnlyList<IStateEffectModel> prevStateEffects,
            IReadOnlyList<IStateEffectModel> newStateEffects)
        {
            var additions = newStateEffects
                .Where(stateEffect => prevStateEffects.All(prev => prev.Id != stateEffect.Id))
                .ToList();

            var existsBuff = additions.Any(stateEffect => stateEffect.Type.ShouldPlayBuffEffect());
            if (existsBuff)
            {
                BattleEffectManager
                    .Generate(BattleEffectId.Buff, transform)
                    .BindCharacterUnit(this)
                    .Play();

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_006);
            }
            
            var existsUnitConditionBuff = additions.Any(stateEffect => stateEffect.Type.IsUnitConditionBuff());
            if (existsUnitConditionBuff)
            {
                BattleEffectManager
                    .Generate(BattleEffectId.UnitConditionBuff, transform)
                    .BindCharacterUnit(this)
                    .Play();

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_006);
            }

            var existsDebuff = additions.Any(stateEffect => stateEffect.Type.ShouldPlayDebuffEffect());
            if (existsDebuff)
            {
                BattleEffectManager
                    .Generate(BattleEffectId.Debuff, transform)
                    .BindCharacterUnit(this)
                    .Play();

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_009);
            }

            var existsPoison = additions.Any(stateEffect => stateEffect.Type == StateEffectType.Poison);
            if (existsPoison)
            {
                BattleEffectManager
                    .Generate(BattleEffectId.Poison, transform)
                    .BindCharacterUnit(this)
                    .Play();

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_026);
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_004);
            }
            var existsBurn = additions.Any(stateEffect => stateEffect.Type == StateEffectType.Burn);
            if (existsBurn)
            {
                BattleEffectManager
                    .Generate(BattleEffectId.Burn, _unitImage.EffectRoot)
                    .BindCharacterUnit(this)
                    .Play();

                SoundEffectPlayer.Play(SoundEffectId.SSE_051_027);
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_004);
            }

            var existsStun = additions.Any(stateEffect => stateEffect.Type == StateEffectType.Stun);
            if (existsStun)
            {
                if (_stunEffect == null)
                {
                    _stunEffect = BattleEffectManager
                        .Generate(BattleEffectId.Stun, transform)
                        ?.BindCharacterUnit(this)
                        .Play();
                }
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_032);
            }
            else if (_stunEffect != null)
            {
                if (newStateEffects.All(stateEffect => stateEffect.Type != StateEffectType.Stun))
                {
                    _stunEffect.Destroy();
                    _stunEffect = null;
                }
            }

            var existsFreeze = additions.Any(stateEffect => stateEffect.Type == StateEffectType.Freeze);
            if (existsFreeze)
            {
                if (_freezeEffect == null)
                {
                    // キャラが凍るエフェクト
                    _freezeEffect = (FreezeEffectView)BattleEffectManager
                        .Generate(BattleEffectId.Freeze, transform)
                        .BindCharacterUnit(this)
                        .Play();
                }
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_031);
            }
            else if (_freezeEffect != null)
            {
                // 氷結解除
                if (newStateEffects.All(stateEffect => stateEffect.Type != StateEffectType.Freeze))
                {
                    _freezeEffect.Destroy();
                    _freezeEffect = null;
                }
            }

            var existsWeakening = additions.Any(stateEffect => stateEffect.Type == StateEffectType.Weakening);
            if (existsWeakening)
            {
                // 弱体化
                if (_weakeningEffect == null)
                {
                    _weakeningEffect = BattleEffectManager
                        .Generate(BattleEffectId.Weakening, transform)
                        ?.BindCharacterUnit(this)
                        .Play();
                }
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_009);
            }
            else if (_weakeningEffect != null)
            {
                if (newStateEffects.All(stateEffect => stateEffect.Type != StateEffectType.Weakening))
                {
                    _weakeningEffect.Destroy();
                    _weakeningEffect = null;
                }
            }
        }

        void GenerateAttackEffect(UnitAttackViewInfo attackViewInfo)
        {
            // 攻撃終了時に消すエフェクト
            var isMirrorAttackEffect = BattleSide == BattleSide.Enemy && attackViewInfo.AttackEffectMirror != null;

            var attackEffect = isMirrorAttackEffect
                ? attackViewInfo.AttackEffectMirror
                : attackViewInfo.AttackEffect;

            if (attackEffect != null)
            {
                _attackEffect = BattleEffectManager
                    .Generate(attackEffect, _unitImage.EffectRoot)
                    ?.BindCharacterUnit(this)
                    ?.BindScreenFlashDelegate(_screenFlashTrackClipDelegate)
                    ?.Play();
            }

            // 攻撃終了後も残るエフェクト（キャラに追従）
            var isMirrorLastingEffect = BattleSide == BattleSide.Enemy && attackViewInfo.AttackLastingEffectMirror != null;

            var attackLastingEffect = isMirrorLastingEffect
                ? attackViewInfo.AttackLastingEffectMirror
                : attackViewInfo.AttackLastingEffect;

            if (attackLastingEffect != null)
            {
                BattleEffectManager
                    .Generate(attackLastingEffect, _unitImage.EffectRoot)
                    ?.BindCharacterUnit(this)
                    ?.BindScreenFlashDelegate(_screenFlashTrackClipDelegate)
                    ?.Play();
            }

            // 攻撃終了後も残るエフェクト（キャラに追従しない）
            var isMirrorStayedLastingEffect =
                BattleSide == BattleSide.Enemy
                && attackViewInfo.AttackStayedLastingEffectMirror != null;

            var attackStayedLastingEffect = isMirrorStayedLastingEffect
                ? attackViewInfo.AttackStayedLastingEffectMirror
                : attackViewInfo.AttackStayedLastingEffect;

            if (attackStayedLastingEffect != null)
            {
                _attackStayedLastingEffect = BattleEffectManager
                    .Generate(attackStayedLastingEffect, _unitImage.EffectRoot)
                    ?.ChangeParent(BattleEffectManager.EffectLayer)
                    ?.BindCharacterUnit(this)
                    ?.BindScreenFlashDelegate(_screenFlashTrackClipDelegate)
                    ?.Play();
            }

            // 擬音、セリフエフェクト
            var isMirrorMangaEffect = BattleSide == BattleSide.Enemy && attackViewInfo.AttackMangaEffectMirror != null;

            var mangaEffect = isMirrorMangaEffect
                ? attackViewInfo.AttackMangaEffectMirror
                : attackViewInfo.AttackMangaEffect;

            if (mangaEffect != null)
            {
                _attackMangaEffect = _pageComponent
                    .GenerateMangaEffect(mangaEffect, FieldViewPos, false)
                    ?.Play();
            }
        }

        void OnHitStopStarted()
        {
            if (!_unitImage.CurrentAnimation.CanHitStop) return;

            _hitStopPauseHandler?.Dispose();
            _hitStopPauseHandler = _unitImage.PauseAnimation();

            _unitImage.Shake(0.1f, 0.05f, 100);

            if (_attackEffect != null)
            {
                _attackEffect.Pause(_hitStopPauseHandler);
            }

            if (_attackMangaEffect != null)
            {
                _attackMangaEffect.Pause(_hitStopPauseHandler);
            }
        }

        void OnHitStopEnded()
        {
            _hitStopPauseHandler?.Dispose();
            _hitStopPauseHandler = null;
        }

        void OnPause(bool isPause)
        {
            _knockBackController.Pause(isPause);
            _hitStopController.Pause(isPause);
        }

        Dictionary<UnitAnimationType, CharacterUnitAnimation> CreateUnitAnimationDictionary(bool isFlip)
        {
            var dictionary = new Dictionary<UnitAnimationType, CharacterUnitAnimation>();

            foreach (UnitAnimationType animationType in Enum.GetValues(typeof(UnitAnimationType)))
            {
                dictionary[animationType] = GetUnitAnimation(animationType, isFlip);
            }

            return dictionary;
        }

        [SuppressMessage("ReSharper", "ConditionIsAlwaysTrueOrFalse")]
        CharacterUnitAnimation GetUnitAnimation(UnitAnimationType animationType, bool isFlip)
        {
            var skeletonData = _unitImage.SkeletonAnimation.state.Data.SkeletonData;

            switch (animationType)
            {
                case UnitAnimationType.Empty:
                    return CharacterUnitAnimation.Empty;
                case UnitAnimationType.Wait:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorWait.Name) != null
                        ? CharacterUnitAnimation.MirrorWait
                        : CharacterUnitAnimation.Wait;
                case UnitAnimationType.WaitJoy:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorWaitJoy.Name) != null
                        ? CharacterUnitAnimation.MirrorWaitJoy
                        : CharacterUnitAnimation.WaitJoy;
                case UnitAnimationType.Move:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorMove.Name) != null
                        ? CharacterUnitAnimation.MirrorMove
                        : CharacterUnitAnimation.Move;
                case UnitAnimationType.Attack:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorAttack.Name) != null
                        ? CharacterUnitAnimation.MirrorAttack
                        : CharacterUnitAnimation.Attack;
                case UnitAnimationType.SpecialAttackCharge:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttackCharge.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttackCharge
                        : CharacterUnitAnimation.SpecialAttackCharge;
                case UnitAnimationType.SpecialAttack:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttack.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttack
                        : CharacterUnitAnimation.SpecialAttack;
                case UnitAnimationType.Damage:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorDamage.Name) != null
                        ? CharacterUnitAnimation.MirrorDamage
                        : CharacterUnitAnimation.Damage;
                case UnitAnimationType.KnockBack:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorKnockBack.Name) != null
                        ? CharacterUnitAnimation.MirrorKnockBack
                        : CharacterUnitAnimation.KnockBack;
                case UnitAnimationType.Death:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorDeath.Name) != null
                        ? CharacterUnitAnimation.MirrorDeath
                        : CharacterUnitAnimation.Death;
                case UnitAnimationType.Escape:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorEscape.Name) != null
                        ? CharacterUnitAnimation.MirrorEscape
                        : CharacterUnitAnimation.Escape;
                case UnitAnimationType.Stun:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorStun.Name) != null
                        ? CharacterUnitAnimation.MirrorStun
                        : CharacterUnitAnimation.Stun;
                case UnitAnimationType.Freeze:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorFreeze.Name) != null
                        ? CharacterUnitAnimation.MirrorFreeze
                        : CharacterUnitAnimation.Freeze;
                default:
                    return CharacterUnitAnimation.Empty;
            }
        }

        bool CanDamageAnimationInterrupt(UnitAnimationType animationType)
        {
            return animationType == UnitAnimationType.Empty
                   || animationType == UnitAnimationType.Wait
                   || animationType == UnitAnimationType.WaitJoy
                   || animationType == UnitAnimationType.Move
                   || animationType == UnitAnimationType.Damage;
        }

        bool CanStartMoveAnimation()
        {
            var currentAnimation = _unitImage.CurrentAnimation;

            return currentAnimation.Type == UnitAnimationType.Wait ||
                   currentAnimation.Type == UnitAnimationType.KnockBack ||
                   currentAnimation.Type == UnitAnimationType.Stun ||
                   currentAnimation.Type == UnitAnimationType.Freeze;
        }

        async UniTask PlayDeathMotion(UnitDeathType deathType, CancellationToken cancellationToken)
        {
            var animationType = deathType　switch
            {
                UnitDeathType.Normal => UnitAnimationType.Death,
                UnitDeathType.Escape => UnitAnimationType.Escape,
                _ => UnitAnimationType.Death
            };

            await _unitImage.PlayAnimation(
                _animationDictionary[animationType],
                CharacterUnitAnimation.Empty,
                cancellationToken);
        }

        void PlayDeathEffect(UnitDeathType deathType)
        {
            var effectId = deathType　switch
            {
                UnitDeathType.Normal => BattleEffectId.CharacterUnitDead,
                UnitDeathType.Escape => BattleEffectId.CharacterUnitEscape,
                _ => BattleEffectId.CharacterUnitDead
            };

            BattleEffectManager
                .Generate(effectId, _unitImage.EffectRoot.position)
                .BindCharacterUnit(this)
                .Play();
        }

        void GenerateAura(CharacterUnitModel unitModel)
        {
            var effectId = BattleEffectId.None;

            if (unitModel.AuraType == UnitAuraType.Default)
            {
                effectId = unitModel.IsBoss ? BattleEffectId.BossAura : BattleEffectId.None;
            }
            else
            {
                effectId = unitModel.AuraType.ToBattleEffectId();
            }

            if (effectId == BattleEffectId.None) return;

            _aura = BattleEffectManager
                .Generate(effectId, _unitImage.EffectRoot)
                ?.BindCharacterUnit(this)
                ?.Play();

            _unitImage.ApplyUnitSizeToSpecifiedBattleEffect(_aura);
        }
    }
}
