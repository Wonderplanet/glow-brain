using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Views.UIAnimator;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public class IdleIncentiveTimeLineAnimationControl : UIObject, IIdleIncentiveBattleUnitTrackClipDelegate
    {
        [Header("TimeLine")]
        [SerializeField] PlayableDirector _playableDirector;
        [SerializeField] PlayableAsset _normalUnitAnimation;
        [SerializeField] PlayableAsset _specialUnitAnimation;
        [SerializeField] Image _background;
        [SerializeField] BattleEffectManager _battleEffectManager;
        [Header("味方Spineデータ")]
        [SerializeField] GameObject _playerCharacterRoot;
        [SerializeField] BattleEffectUsableUISpineWithOutlineAvatar _playerBattleEffectUsableUISpineAvatar;
        [Header("敵Spineデータ")]
        [SerializeField] GameObject _enemyCharacterRoot;
        [SerializeField] BattleEffectUsableUISpineWithOutlineAvatar _enemyBattleEffectUsableUISpineAvatar;
        [SerializeField] UIAnimator _specialEnemyAnimator;

        static Vector3 EffectScale => new Vector3(100, 100, 1);
        static float EnemyDeathEffectTime => 0.8f;
        static float AttackAreaWidth => 600f;

        TickCount _attackDelay;
        AttackRange _attackRange;
        UnitAttackViewInfo _attackViewInfo;

        Material _backgroundMaterial;

        protected override void OnDestroy()
        {
            base.OnDestroy();

            if (_backgroundMaterial != null)
            {
                Destroy(_backgroundMaterial);
            }
        }

        public async UniTask InitializeBattleEffectManager(CancellationToken cancellationToken)
        {
            await _battleEffectManager.Initialize(cancellationToken);
        }

        public void SetupBackground(KomaBackgroundAssetPath assetPath)
        {
            UISpriteUtil.LoadSpriteWithFade(_background, assetPath.Value, () =>
            {
                if (!_background || _background.sprite == null) return;

                _backgroundMaterial = new Material(_background.material);
                _background.material = _backgroundMaterial;
                _background.mainTexture.wrapMode = TextureWrapMode.Repeat;

                var rect = _background.GetComponent<RectTransform>();
                var sizeDelta = rect.sizeDelta;
                sizeDelta.y = _background.sprite.texture.height;
                rect.sizeDelta = sizeDelta;

                var pos = rect.anchoredPosition;
                pos.y = sizeDelta.y / 2;
                rect.anchoredPosition = pos;
            });
            _background.gameObject.SetActive(true);
        }

        public void SetupPlayableAsset(CharacterUnitRoleType roleType)
        {
            if (roleType == CharacterUnitRoleType.Special)
            {
                _playableDirector.playableAsset = _specialUnitAnimation;
                _specialEnemyAnimator.enabled = true;
            }
            else
            {
                _playableDirector.playableAsset = _normalUnitAnimation;
                _specialEnemyAnimator.enabled = false;
            }
        }

        public void SetupAnimation(
            UnitImage playerUnit,
            UnitImage enemyUnit,
            TickCount attackDelay,
            AttackRange attackRange,
            UnitAttackViewInfo attackViewInfo,
            PhantomizedFlag enemyIsPhantomized)
        {
            _attackDelay = attackDelay;
            _attackRange = attackRange;
            _attackViewInfo = attackViewInfo;

            _playerBattleEffectUsableUISpineAvatar.Build(playerUnit);
            _enemyBattleEffectUsableUISpineAvatar.Build(enemyUnit);

            InitializeAvatar(_playerBattleEffectUsableUISpineAvatar, _playerCharacterRoot.transform, false);
            InitializeAvatar(_enemyBattleEffectUsableUISpineAvatar, _enemyCharacterRoot.transform, true);

            // 敵のPhantomize設定を適用
            _enemyBattleEffectUsableUISpineAvatar.SetPhantomized(enemyIsPhantomized);
            // 敵の移動エフェクトマスク設定
            _enemyBattleEffectUsableUISpineAvatar.SetEffectMaskSetting(SpriteMaskInteraction.VisibleInsideMask);

            _playableDirector.Play();
        }

        void InitializeAvatar(BattleEffectUsableUISpineWithOutlineAvatar avatar, Transform parent, bool flip)
        {
            avatar.transform.localPosition = Vector3.zero;
            avatar.transform.SetParent(parent, false);
            avatar.StartAnimation(CharacterUnitAnimation.Wait, CharacterUnitAnimation.Empty);
            avatar.Flip = flip;
        }

        void IIdleIncentiveBattleUnitTrackClipDelegate.OnPlay(double duration)
        {
            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async ct => RunBattleUnitAnimation(ct, duration));
        }

        async UniTask RunBattleUnitAnimation(CancellationToken ct, double duration)
        {
            await AnimationMoveEnemyUnit(ct, duration);

            await AnimationAttackPlayerUnit(ct);

            await AnimationDeadEnemyUnit(ct);

            _enemyBattleEffectUsableUISpineAvatar.transform.localPosition = Vector3.zero;
        }

        async UniTask AnimationMoveEnemyUnit(CancellationToken ct, double duration)
        {
            // 敵キャラ移動
            _enemyBattleEffectUsableUISpineAvatar.InstancedUnitImage.SetConstantlyEffectAnimatorsEnabled(true);
            _enemyBattleEffectUsableUISpineAvatar.StartAnimation(CharacterUnitAnimation.Move, CharacterUnitAnimation.Empty);
            var moveAreaWidth = _playerCharacterRoot.transform.localPosition.x - _enemyCharacterRoot.transform.localPosition.x;
            var attackLength = _attackRange.EndPointType == AttackRangePointType.Distance
                ? AttackAreaWidth * _attackRange.EndPointParameter.Value
                : AttackAreaWidth * 0.5f;

            // TimeLine上で設定した再生時間から、キャラモーション等に最低限必要な再生時間を引いて残りの時間を移動時間に当てる
            var playerAttackDelay = _attackDelay.ToSeconds();
            var animationDuration = _playerBattleEffectUsableUISpineAvatar.GetAnimationDuration(CharacterUnitAnimation.Attack);
            var actionDuplicateTime = animationDuration - _attackDelay.ToSeconds();

            var moveTime = (float)(duration + actionDuplicateTime
                - _playerBattleEffectUsableUISpineAvatar.GetAnimationDuration(CharacterUnitAnimation.Attack)
                - _enemyBattleEffectUsableUISpineAvatar.GetAnimationDuration(CharacterUnitAnimation.Death)
                - EnemyDeathEffectTime);   // 死亡エフェクト分は固定値で引いておく

            var movedPosition = _enemyBattleEffectUsableUISpineAvatar.transform.localPosition;
            movedPosition.x += moveAreaWidth - attackLength;
            _enemyBattleEffectUsableUISpineAvatar.transform
                .DOLocalMove(movedPosition, moveTime)
                .SetEase(Ease.Linear);

            var diffTime = moveTime - playerAttackDelay;
            var waitTime = diffTime > 0 ? diffTime : 0;
            await UniTask.Delay(TimeSpan.FromSeconds(waitTime), cancellationToken:ct);
        }

        async UniTask AnimationAttackPlayerUnit(CancellationToken ct)
        {
            // 敵キャラ待機モーション+プレイヤーキャラ攻撃モーション
            // 敵キャラが射程に入るタイミングと攻撃モーションをあわせる
            _playerBattleEffectUsableUISpineAvatar.StartAnimation(CharacterUnitAnimation.Attack, CharacterUnitAnimation.Wait);
            if(_attackViewInfo != null && _attackViewInfo.AttackEffect != null)
            {
                //SkeletonAnimationをGUIの後ろに生成している関係上
                //そのままだとz:1でGUIの後ろに生成されるので明示的に手前に持ってくる
                _battleEffectManager
                    .Generate(
                        _attackViewInfo.AttackEffect,
                        _playerBattleEffectUsableUISpineAvatar.EffectRoot,
                        new Vector3(0,0,-1))
                    .BindCharacterImage(_playerBattleEffectUsableUISpineAvatar.InstancedUnitImage)
                    .Play();
            }

            // ダメージ発生と同時にエフェクト再生&敵キャラ死亡
            await UniTask.Delay(TimeSpan.FromSeconds(_attackDelay.ToSeconds()), cancellationToken:ct);
        }

        async UniTask AnimationDeadEnemyUnit(CancellationToken ct)
        {
            //SkeletonAnimationをGUIの後ろに生成している関係上
            //そのままだとz:1でGUIの後ろに生成されるので明示的に手前に持ってくる
            var centerPos = _enemyBattleEffectUsableUISpineAvatar.EffectRootPosition;
            centerPos.z = -1f;
            ShowBattleEffect(
                BattleEffectId.CommonHit01,
                centerPos,
                _enemyBattleEffectUsableUISpineAvatar.InstancedUnitImage,
                true);

            bool isEndEnemyDeadAnimation = false;
            _enemyBattleEffectUsableUISpineAvatar.StartAnimation(CharacterUnitAnimation.Death, CharacterUnitAnimation.Empty,
                () => isEndEnemyDeadAnimation = true);

            await UniTask.WaitUntil(() => isEndEnemyDeadAnimation, cancellationToken:ct);

            //SkeletonAnimationをGUIの後ろに生成している関係上
            //そのままだとz:1でGUIの後ろに生成されるので明示的に手前に持ってくる
            var unitPos = _enemyBattleEffectUsableUISpineAvatar.transform.position;
            unitPos.z = -1f;
            var deathEffect = ShowBattleEffect(
                BattleEffectId.CharacterUnitDead,
                unitPos,
                _enemyBattleEffectUsableUISpineAvatar.InstancedUnitImage,
                true);

            if (deathEffect == null) return;

            _enemyBattleEffectUsableUISpineAvatar.InstancedUnitImage.SetConstantlyEffectAnimatorsEnabled(false);

            // _enemyBattleEffectUsableUISpineAvatar.gameObject.SetActive(false);
            bool isEndDeathEffect = false;
            deathEffect.AddCompletedAction(() => isEndDeathEffect = true);

            //インターバル待ち
            await UniTask.WaitUntil(() => isEndDeathEffect, cancellationToken:ct);
        }

        BaseBattleEffectView ShowBattleEffect(
            BattleEffectId effectId,
            Vector3 position,
            UnitImage bindUnitImage = null,
            bool isScale = true)
        {
            var effect = _battleEffectManager.Generate(effectId, position);
            if(effect == null) return null;

            if(null != bindUnitImage)
            {
                effect.BindCharacterImage(bindUnitImage);
            }

            if(isScale)
            {
                effect.transform.localScale = EffectScale;
            }

            effect.Play();
            return effect;
        }
    }
}
