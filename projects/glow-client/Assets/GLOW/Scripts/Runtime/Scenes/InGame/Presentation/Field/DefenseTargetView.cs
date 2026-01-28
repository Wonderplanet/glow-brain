using System.Collections.Generic;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class DefenseTargetView : MonoBehaviour
    {
        const float DefaultYPosition = 1.5f;
        const float DangerHpRate = 0.3f;

        [SerializeField] GameObject _imageRoot;
        [SerializeField] DefenseTargetHpView _hpView;
        [SerializeField] GameObject _targetLabel;
        [SerializeField] DamageOnomatopoeiaComponent _damageOnomatopoeiaPrefab;

        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] IDefenseTargetImageContainer DefenseTargetImageContainer { get; }
        
        PageComponent _pageComponent;

        HP _maxHp;
        Tween _tween;
        float _defaultLocalZPos;

        public FieldViewCoordV2 FieldViewPos
        {
            get
            {
                var pos = transform.localPosition;
                return new FieldViewCoordV2(pos.x, pos.y);
            }
        }

        public void Initialize(
            DefenseTargetModel defenseTargetModel, 
            IViewCoordinateConverter viewCoordinateConverter,
            PageComponent pageComponent)
        {
            _pageComponent = pageComponent;
            
            _maxHp = defenseTargetModel.MaxHp;

            var imagePrefab = DefenseTargetImageContainer.Get(defenseTargetModel.AssetKey);
            Instantiate(imagePrefab, _imageRoot.transform, false).GetComponent<DefenseTargetImage>();

            var pos = viewCoordinateConverter.ToFieldViewCoord(defenseTargetModel.BattleSide, defenseTargetModel.Pos);
            transform.localPosition = new Vector3(pos.X, DefaultYPosition, 0.0f);
            
            _defaultLocalZPos = transform.localPosition.z;

            _hpView.Initialize(defenseTargetModel.BattleSide);
            _hpView.SetHpText(defenseTargetModel.MaxHp);
        }

        /// <summary> バトル開始時の強調表示 </summary>
        public void SetDefenseTargetDisplayHighlight(bool isHighlight)
        {
            if (isHighlight)
            {
                var pos = transform.position;
                pos.z = FieldZPositionDefinitions.Highlight;
                transform.position = pos;
            }
            else
            {
                var pos = transform.localPosition;
                pos.z = _defaultLocalZPos;
                transform.localPosition = pos;
            }

            _hpView.gameObject.SetActive(!isHighlight);
            _targetLabel.SetActive(isHighlight);
        }

        public void SetHp(HP hp)
        {
            bool isDangerHp = IsDangerHp(hp);
            _hpView.SetHpText(hp);
            _hpView.SwitchDanger(isDangerHp);
        }

        public void OnHitAttacks(HP hp, IReadOnlyList<AppliedAttackResultModel> appliedAttackResultModels)
        {
            if (hp.IsZero())
            {
                return;
            }

            // 振動
            _tween?.Kill(true);
            _tween = _imageRoot.transform.DOShakePosition(0.5f, 0.2f, 50, 90f, false, true);

            // ダメージ擬音・SE
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_007);

            foreach (var appliedAttackResultModel in appliedAttackResultModels)
            {
                var onomatopoeiaAssetKey = PickAttackHitOnomatopoeia(appliedAttackResultModel.AttackHitData);
                if (onomatopoeiaAssetKey.IsEmpty()) continue;

                _pageComponent
                    .GenerateMangaEffect(_damageOnomatopoeiaPrefab, FieldViewPos, false)
                    ?.Setup(onomatopoeiaAssetKey, CharacterColor.None, false)
                    ?.Play();
            }
        }

        bool IsDangerHp(HP hp)
        {
            return hp <= _maxHp * DangerHpRate;
        }
        
        AttackHitOnomatopoeiaAssetKey PickAttackHitOnomatopoeia(AttackHitData attackHitData)
        {
            var onomatopoeiaAssetKeys = attackHitData.OnomatopoeiaAssetKeys;
            if (onomatopoeiaAssetKeys.Count == 0) return AttackHitOnomatopoeiaAssetKey.Empty;

            return onomatopoeiaAssetKeys[UnityEngine.Random.Range(0, onomatopoeiaAssetKeys.Count)];
        }
    }
}
