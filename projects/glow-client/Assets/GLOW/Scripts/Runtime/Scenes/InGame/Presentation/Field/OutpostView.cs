using System.Collections.Generic;
using System.Linq;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WonderPlanet.RandomGenerator;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class OutpostView : MonoBehaviour
    {
        const float DangerHpRate = 0.3f;

        static readonly int DangerHpAnimationTrigger = Animator.StringToHash("dangerHp");
        static readonly Vector3 BreakDownEffectOffset = new (0, 0.5f, 0);
        [SerializeField] Animator _animator;
        [SerializeField] GameObject _outpostSpriteRoot;
        [SerializeField] OutpostHpView _outpostHpView;
        [SerializeField] DamageOnomatopoeiaComponent _damageOnomatopoeiaPrefab;

        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] IOutpostViewInfoContainer OutpostViewInfoContainer { get; }
        [Inject] PrefabFactory<OutpostSpriteView> OutpostSpriteViewFactory { get; }
        [Inject] IRandomizer Randomizer { get; }

        PageComponent _pageComponent;
        BattleSide _battleSide;
        OutpostDamageInvalidationFlag _damageInvalidationFlag;  // ダメージ無効化時のクエストはtrue
        IOutpostSpriteView _showSpriteView;
        HP _maxHp;
        Tween _tween;

        public FieldObjectId Id { get; private set; }
        public FieldViewCoordV2 FieldViewPos => new (transform.localPosition.x, 0f);

        public void InitializeOutpostView(OutpostModel outpostModel, PageComponent pageComponent)
        {
            _pageComponent = pageComponent;

            Id = outpostModel.Id;
            _battleSide = outpostModel.BattleSide;
            _maxHp = outpostModel.MaxHp;
            _damageInvalidationFlag = outpostModel.DamageInvalidationFlag;

            if (_damageInvalidationFlag.IsDamageInvalidation())
            {
                // ダメージ無効化時のクエストでは表記を???にする
                _outpostHpView.SetInvisibleHPText();
            }
            else
            {
                _outpostHpView.Initialize(_battleSide);
                _outpostHpView.SetHpText(outpostModel.Hp);
            }

            var myTransform = transform;
            var scale = myTransform.localScale;
            myTransform.localScale = new Vector3(scale.x, scale.y, scale.z * 0.1f);

            // ゲート見た目Prefab読み込み。defaultではOutpostFactoryでPrefabが定義されている。ステージマスターによってはこれが変更される形
            _showSpriteView = LoadStageDependentPrefab(outpostModel.OutpostAssetKey);
            if (!outpostModel.ArtworkAssetPath.IsEmpty())
            {
                _showSpriteView.SetArtworkSprite(outpostModel.ArtworkAssetPath);
            }
        }

        /// <summary> ステージ個別でゲートの見た目を変更するときに使用 /// </summary>
        IOutpostSpriteView LoadStageDependentPrefab(OutpostAssetKey assetKey)
        {
            var viewInfo = OutpostViewInfoContainer.Get(assetKey);
            var spriteView = OutpostSpriteViewFactory.Create(viewInfo.OutpostPrefab);
            spriteView.transform.SetParent(_outpostSpriteRoot.transform, false);
            spriteView.Initialize(_outpostSpriteRoot, _battleSide, viewInfo, _pageComponent);
            return spriteView;
        }

        public void Recover()
        {
            // 破壊演出で消されてるので表示し直す
            _outpostSpriteRoot.SetActive(true);
            _showSpriteView.OnRecover();
        }

        public void GenerateSummonEffect()
        {
            _showSpriteView.OnSummonUnit();
        }

        public void UpdateHp(HP hp, HP maxHp)
        {
            _maxHp = maxHp;

            if (_damageInvalidationFlag.IsDamageInvalidation())
            {
                _outpostHpView.SetInvisibleHPText();
                return;
            }

            bool isDangerHp = IsDangerHp(hp);

            _outpostHpView.SetHpText(hp);
            _outpostHpView.SwitchDanger(isDangerHp);

            _animator.SetBool(DangerHpAnimationTrigger, isDangerHp);
        }

        public void OnHitAttacks(HP hp, IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            UpdateHp(hp, _maxHp);

            if (hp.IsZero())
            {
                return;
            }

            var isDamage = appliedAttackResultModels.Any(res => res.AttackDamageType != AttackDamageType.None);
            if (!isDamage) return;

            _showSpriteView.OnHitAttacks(IsDangerHp(hp));

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_007);

            // ダメージ擬音
            GenerateAttackHitOnomatopoeia(appliedAttackResultModels);
            
            // ダメージ数値アニメーション
            PlayDamageNumberAnimation(appliedAttackResultModels);
        }

        public void OnBreakDown()
        {
            _showSpriteView.OnBreakDown(FieldViewPos, BreakDownEffectOffset);
        }

        public void SetPlayerOutpostHpHighlight(bool isHighlight)
        {
            _outpostHpView.SetPlayerOutpostHpHighlight(isHighlight);
        }

        bool IsDangerHp(HP hp)
        {
            return hp <= _maxHp * DangerHpRate;
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
                    ?.Setup(onomatopoeiaAssetKey, CharacterColor.None, false)
                    ?.Play();
            }
        }
        
        void PlayDamageNumberAnimation(IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            foreach (var appliedAttackResultModel in appliedAttackResultModels)
            {
                var offsetPos = new FieldViewCoordV2(
                    Randomizer.Range(-0.5f, 0.5f),
                    Randomizer.Range(1.0f, 2.0f));
                _pageComponent.GenerateDamageNumberEffect(
                        FieldViewPos, 
                        offsetPos, 
                        appliedAttackResultModel)
                    ?.Play();
            }
        }

        AttackHitOnomatopoeiaAssetKey PickAttackHitOnomatopoeia(AttackHitData attackHitData)
        {
            var onomatopoeiaAssetKeys = attackHitData.OnomatopoeiaAssetKeys;
            if (onomatopoeiaAssetKeys.Count == 0) return AttackHitOnomatopoeiaAssetKey.Empty;

            return onomatopoeiaAssetKeys[UnityEngine.Random.Range(0, onomatopoeiaAssetKeys.Count)];
        }
    }
}
