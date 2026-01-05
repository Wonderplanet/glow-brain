using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
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
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary> ロールがスペシャルのユニット用のインゲーム内表示用View </summary>
    public class FieldSpecialUnitView : MonoBehaviour, IFieldViewPagePositionTrackerTarget
    {
        // ユニットの初期座標(横:Xは配置するコマの中心)
        static readonly float DefaultYPosition = 0.1f;

        [SerializeField] Transform _characterImageRoot;
        [SerializeField] FieldUnitShadowTrace _shadowObj;

        [Inject] IViewCoordinateConverter ViewCoordinateConverter { get; }
        [Inject] BattleEffectManager BattleEffectManager { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }

        IScreenFlashTrackClipDelegate _screenFlashTrackClipDelegate;
        PageComponent _pageComponent;

        // エフェクト群
        UnitAttackViewInfoSet _unitAttackViewInfoSet;
        BaseBattleEffectView _specialAttackAuraEffect;
        BaseBattleEffectView _attackEffect;
        BaseBattleEffectView _attackStayedLastingEffect;
        AbstractMangaEffectComponent _attackMangaEffect;

        Dictionary<UnitAnimationType, CharacterUnitAnimation> _animationDictionary = new ();
        UnitImage _unitImage;
        UnitTagPosition _unitTagPosition;
        BattleSide _battleSide;
        CharacterColor _unitColor;
        AttackData _attackData;
        KomaModel _komaModel;

        // 必殺技発動およびカットイン表示の割り込みが開始されたか。カットイン終了後に必殺技演出を表示したいため待機に使用
        SpecialUnitUseSpecialAttackFlag _isSpecialUnitCutInInterruptionStartedFlag = SpecialUnitUseSpecialAttackFlag.False;

        readonly MultipleSwitchController _pauseController = new ();
        float _pauseTime = 0;
        float _pauseDelayTime = 0;

        public FieldObjectId Id { get; private set; }
        public SkeletonAnimation SkeletonAnimation => _unitImage.SkeletonAnimation;
        public UnitImage UnitImage => _unitImage;
        public CharacterColor UnitColor => _unitColor;
        public BattleSide BattleSide => _battleSide;

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

        void Awake()
        {
            _pauseController.OnStateChanged = OnPause;
        }

        void OnDestroy()
        {
            _shadowObj.Clear();
            _pauseController.Dispose();
        }

        public void Initialize(
            SpecialUnitModel specialUnitModel,
            UnitImage unitImage,
            PageComponent pageComponent,
            IScreenFlashTrackClipDelegate screenFlashTrackClipDelegate)
        {
            _pageComponent = pageComponent;
            _screenFlashTrackClipDelegate = screenFlashTrackClipDelegate;

            Id = specialUnitModel.Id;
            _unitImage = unitImage;
            _battleSide = specialUnitModel.BattleSide;
            _unitColor = specialUnitModel.Color;
            _attackData = specialUnitModel.SpecialAttack;
            _komaModel = specialUnitModel.LocatedKoma;

            // 座標周り設定
            var characterImageTransform = _unitImage.transform;
            characterImageTransform.parent = _characterImageRoot;
            characterImageTransform.localPosition = Vector3.zero;
            characterImageTransform.localScale = Vector3.one;

            // 影の初期化
            _shadowObj.RegisterSkeletonAnimation(SkeletonAnimation);
            _shadowObj.SetupShadowColor(specialUnitModel.Color);

            // アニメーション設定
            var isFlip = specialUnitModel.BattleSide == BattleSide.Enemy;
            _unitImage.Flip = isFlip;
            _animationDictionary = CreateUnitAnimationDictionary(isFlip);
            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.Wait], CharacterUnitAnimation.Empty);

            _unitImage.Color = new Color(1, 1, 1, 0);

            _unitTagPosition = new UnitTagPosition(_unitImage);

            SetPos(specialUnitModel, ViewCoordinateConverter);

            _unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(specialUnitModel.AssetKey);
        }

        /// <summary> 召喚エフェクト表示とキャラクターフェードイン </summary>
        public async UniTask Show(CancellationToken token)
        {
            BattleEffectManager.Generate(BattleEffectId.SpecialUnitSummon, _unitImage.EffectRoot).BindSpecialUnit(this).Play();

            // 召喚エフェクト途中からキャラクターをフェードインし始める
            const float fadeStartWaitTime = 0.5f;
            var setColor = new Color(1.0f, 1.0f, 1.0f, 1.0f);
            await WaitTime(fadeStartWaitTime, token);
            DOTween.To(() => _unitImage.Color, color => _unitImage.Color = color, setColor, 0.2f).SetEase(Ease.InOutQuad);

            _shadowObj.FadeIn(0.15f);

            await WaitTime(SpecialUnitModel.ShowHideTime.ToSeconds() - fadeStartWaitTime, token);
        }

        /// <summary> 退去エフェクト表示とキャラクターフェードアウト </summary>
        public async UniTask Hide(CancellationToken token)
        {
            BattleEffectManager.Generate(BattleEffectId.SpecialUnitLeaving, _unitImage.EffectRoot).BindSpecialUnit(this).Play();

            // 退去エフェクト途中からキャラクターをフェードアウトし始める
            const float fadeStartWaitTime = 0.36f;
            await WaitTime(fadeStartWaitTime, token);
            var setColor = new Color(1.0f, 1.0f, 1.0f, 0.0f);
            DOTween.To(() => _unitImage.Color, color => _unitImage.Color = color, setColor, 0.2f).SetEase(Ease.InOutQuad);

            _shadowObj.FadeOut(0.15f);

            await WaitTime(SpecialUnitModel.ShowHideTime.ToSeconds() - fadeStartWaitTime, token);
        }

        /// <summary> 必殺技開始と溜め </summary>
        public async UniTask OnStartSpecialAttackCharge(CancellationToken token)
        {
            ResetEffects();

            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.SpecialAttackCharge], CharacterUnitAnimation.Empty);
            ShowAttackAuraEffect();

            await WaitTime(CharacterUnitAttackChargeAction.InitialChargeTime.ToSeconds() - TickCount.One.ToSeconds(), token);
        }

        /// <summary> 必殺技実行 </summary>
        public async UniTask OnSpecialAttack(
            CancellationToken token,
            Action onStartSpecialAttack,
            Action onEndSpecialAttack)
        {
            // データ側が必殺技発動とカットイン開始タイミングになるまで待機
            // カットインより先にエフェクトが先に生成されてしまうのを防ぐためにも必要となる
            await UniTask.WaitUntil(() => _isSpecialUnitCutInInterruptionStartedFlag, cancellationToken: token);

            // カットイン終了によるポーズ解除まで待機（カットイン演出が無ければそのままスルー）
            await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken: token);
            await UniTask.WaitWhile(() => _pauseController.IsOn(), cancellationToken: token);

            ResetForSpecialAttack();
            _unitImage.StartAnimation(_animationDictionary[UnitAnimationType.SpecialAttack], _animationDictionary[UnitAnimationType.Wait] );

            // 同じタイミングで召喚されるキャラなどもPauseさせるために待つ
            await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken: token);
            onStartSpecialAttack?.Invoke();
            await UniTask.Yield(cancellationToken: token);

            // 半透明黒の前に出しつつエフェクト生成
            var specialAttackPosition = transform.position;
            specialAttackPosition.z = FieldZPositionDefinitions.SpecialUnitSpecialAttack;
            transform.position = specialAttackPosition;

            if (_unitAttackViewInfoSet != null)
            {
                GenerateAttackEffect(_unitAttackViewInfoSet.SpecialAttackViewInfo);
            }

            // 必殺技自体の時間待ち
            await WaitTime(_attackData.BaseData.ActionDuration.ToSeconds(), token);
            ResetEffects();
            onEndSpecialAttack?.Invoke();

            var originPosition = transform.position;
            originPosition.z = FieldZPositionDefinitions.SpecialUnit;
            transform.position = originPosition;

            // スペシャルユニット特有の発動後の待機時間
            await WaitTime(SpecialUnitModel.EndSpecialAttackedWaitTime.ToSeconds(), token);
        }

        public void OnSpecialUnitCutInInterruptionStarted()
        {
            _isSpecialUnitCutInInterruptionStartedFlag = SpecialUnitUseSpecialAttackFlag.True;
        }

        /// <summary> ポーズ設定 </summary>
        public MultipleSwitchHandler PauseAnimation(MultipleSwitchHandler handler)
        {
            _pauseController.TurnOn(handler);
            _unitImage.PauseAnimation(handler);

            return handler;
        }

        public BaseBattleEffectView OnRushAttackPowerUp()
        {
            Vector3 setPos = _unitImage.TagPosition.position;
            setPos.z = transform.position.z;
            var effectView = BattleEffectManager
                .Generate(BattleEffectId.RushAttackPowerUp, setPos)
                ?.BindSpecialUnit(this)
                ?.Play();

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_034);

            return effectView;
        }

        /// <summary> 必殺技用エフェクト生成 </summary>
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
                    ?.BindSpecialUnit(this)
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
                    ?.BindSpecialUnit(this)
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
                    ?.BindSpecialUnit(this)
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

        void OnPause(bool isPause)
        {
            if (isPause)
            {
                _pauseTime = Time.time;
            }
            else
            {
                _pauseDelayTime += Time.time - _pauseTime;
            }
        }

        /// <summary> 座標設定 </summary>
        void SetPos(SpecialUnitModel specialUnitModel, IViewCoordinateConverter viewCoordinateConverter)
        {
            var pos = viewCoordinateConverter.ToFieldViewCoord(specialUnitModel.BattleSide, specialUnitModel.Pos);
            transform.localPosition = new Vector3(
                pos.X,
                DefaultYPosition,
                0f);

            var originPosition = transform.position;
            originPosition.z = FieldZPositionDefinitions.SpecialUnit;
            transform.position = originPosition;
        }

        /// <summary> 必殺技溜め中のオーラエフェクト表示 </summary>
        void ShowAttackAuraEffect()
        {
            if (_specialAttackAuraEffect != null) return;

            _specialAttackAuraEffect = BattleEffectManager
                .Generate(CharacterUnitRoleType.Special.GetSpecialAttackAuraEffectId(), _unitImage.EffectRoot)
                ?.BindSpecialUnit(this)
                ?.Play();

            _unitImage.ApplyUnitSizeToSpecifiedBattleEffect(_specialAttackAuraEffect);
        }

        void ResetEffects()
        {
            DestroyEffects();
        }

        /// <summary> 必殺技周りのリセット処理 </summary>
        void ResetForSpecialAttack()
        {
            DestroyEffectsForSpecialAttack();
        }

        /// <summary> 生成・表示中のエフェクト群削除 </summary>
        void DestroyEffects()
        {
            DestroyAttackAuraEffect();
            DestroyEffectsForSpecialAttack();
        }

        /// <summary> 必殺技溜め中のオーラエフェクト削除 </summary>
        void DestroyAttackAuraEffect()
        {
            if (_specialAttackAuraEffect != null)
            {
                _specialAttackAuraEffect.Destroy();
                _specialAttackAuraEffect = null;
            }
        }

        /// <summary> 必殺技のエフェクト削除 </summary>
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

        /// <summary> アニメーション一覧作成 </summary>
        Dictionary<UnitAnimationType, CharacterUnitAnimation> CreateUnitAnimationDictionary(bool isFlip)
        {
            var dictionary = new Dictionary<UnitAnimationType, CharacterUnitAnimation>();

            foreach (UnitAnimationType animationType in Enum.GetValues(typeof(UnitAnimationType)))
            {
                dictionary[animationType] = GetUnitAnimation(animationType, isFlip);
            }

            return dictionary;
        }

        /// <summary> 向きに合わせたアニメーションタイプの取得。FieldUnitView側と異なり、ダメージモーション等は無しに（間違っても呼ばれないように） </summary>
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
                case UnitAnimationType.SpecialAttackCharge:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttackCharge.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttackCharge
                        : CharacterUnitAnimation.SpecialAttackCharge;
                case UnitAnimationType.SpecialAttack:
                    return isFlip && skeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttack.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttack
                        : CharacterUnitAnimation.SpecialAttack;
                default:
                    return CharacterUnitAnimation.Empty;
            }
        }

        /// <summary> UniTask.Delayで対応できないポーズを考慮するための待ち処理 </summary>
        async UniTask WaitTime(float seconds, CancellationToken token)
        {
            var startTime = Time.time;
            var endTime = startTime + seconds;
            var currentTime = startTime;
            _pauseDelayTime = 0;

            while (endTime + _pauseDelayTime > currentTime)
            {
                await UniTask.Yield(token);
                token.ThrowIfCancellationRequested();

                if (!_pauseController.IsOn())
                {
                    currentTime = Time.time;
                }
            }
        }

        // IFieldViewPagePositionTrackerTarget implementation
        public FieldViewCoordV2 GetFieldViewCoordPos()
        {
            return FieldViewPos;
        }

        public bool IsDestroyed()
        {
            return this == null;
        }
    }
}
