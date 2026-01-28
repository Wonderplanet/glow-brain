using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    [Serializable]
    public struct GachaContentAssetPickupAreaInformation
    {
        public GameObject pickUpGameObject;
        public string pickupMstUnitId;
    }

    public class GachaContentAssetComponent : MonoBehaviour
    {
        [SerializeField] GameObject _gachaIntroductionGameObject;
        [SerializeField] GachaContentAssetPickupAreaInformation[] _pickupAreaInformations;

        const string IntroAnimationName = "Gasha-Top-Event_Pick00";
        const string PickupAnimationNameFormat = "Gasha-Top-Event_Pick0{0}";

        Animator _animator;
        int _currentPickupIndex = -1; // -1 = Intro状態、0以上 = ピックアップアニメーション中
        bool HasPickupAreaInformation => _pickupAreaInformations != null && 1 <= _pickupAreaInformations.Length;

        public IReadOnlyList<MasterDataId> PickupMstUnitIds
        {
            get
            {
                if (!HasPickupAreaInformation)
                {
                    return new List<MasterDataId>();
                }

                return _pickupAreaInformations
                    .Select(p => new MasterDataId(p.pickupMstUnitId))
                    .ToList();
            }
        }

        public MasterDataId CurrentPickupMstUnitId
        {
            get
            {
                if (!HasPickupAreaInformation || _currentPickupIndex < 0 || _currentPickupIndex >= _pickupAreaInformations.Length)
                {
                    return MasterDataId.Empty;
                }

                return new MasterDataId(_pickupAreaInformations[_currentPickupIndex].pickupMstUnitId);
            }
        }

        // boolは「ピックアップ対象アニメーションか？」を返す(ガシャ紹介アニメーション時はfalse)
        public Action<bool> OnAnimationStart { get; private set; }

        public void InitializeView(Action<bool> onAnimationStart)
        {
            OnAnimationStart = onAnimationStart;
            _animator = GetComponent<Animator>();

            PlayGachaIntroductionAnimation();
        }

        public void NextPickupAreaInformation()
        {
            if (!HasPickupAreaInformation)
            {
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

            // 処理を委譲させて、副作用管理を一元化
            OnEndAnimationFromPickup();
        }

        // アニメーション終了コールバック(animationClipから呼ばれる想定)
        public void OnEndAnimationFromIntroduction()
        {
            if (!HasPickupAreaInformation)
            {
                // ピックアップ対象なければガシャ紹介アニメーション再度流す
                PlayGachaIntroductionAnimation();
                return;
            }
            PlayPickupUnitAnimation(1);

        }

        // アニメーション終了コールバック(animationClipから呼ばれる想定)
        public void OnEndAnimationFromPickup()
        {
            if (!HasPickupAreaInformation)
            {
                return;
            }

            // 次のピックアップインデックスを計算（0始まり）
            var nextIndex = _currentPickupIndex + 1;

            // 次のアニメーション番号（1始まり）
            var nextNumber = nextIndex + 1;

            if (!ShouldShowNextPickupAnimation(nextNumber))
            {
                PlayGachaIntroductionAnimation();
                return;
            }

            PlayPickupUnitAnimation(nextNumber);
        }

        void PlayGachaIntroductionAnimation()
        {
            OnAnimationStart?.Invoke(false);

            // Intro状態に戻す
            UpdateCurrentPickupIndex(-1);

            //ゲームオブジェクト表示更新
            _gachaIntroductionGameObject.SetActive(true);
            foreach (var info in _pickupAreaInformations)
            {
                info.pickUpGameObject.SetActive(false);
            }

            //ガシャ紹介アニメーションを流す処理
            _animator.Play(IntroAnimationName, 0, 0);
        }

        void PlayPickupUnitAnimation(int number)
        {
            OnAnimationStart?.Invoke(true);

            // number は 1始まりなので、配列アクセス用に 0始まりに変換
            var arrayIndex = number - 1;

            // 副作用: 現在のピックアップインデックスを更新
            UpdateCurrentPickupIndex(arrayIndex);

            //ゲームオブジェクト表示更新
            _gachaIntroductionGameObject.SetActive(true);

            var currentInfo = _pickupAreaInformations[arrayIndex];
            foreach (var info in _pickupAreaInformations)
            {
                info.pickUpGameObject.SetActive(info.pickupMstUnitId == currentInfo.pickupMstUnitId);
            }

            //ピックアップユニットアニメーションを流す処理
            _animator.Play(string.Format(PickupAnimationNameFormat, number), 0, 0);
        }

        bool ShouldShowNextPickupAnimation(int nextNumber)
        {
            // ピックアップ情報がなければfalse
            if(!HasPickupAreaInformation) return false;

            // nextNumber 0 はIntroアニメーションなのでfalse
            if(nextNumber == 0) return false;

            // nextNumber が 1～Length の範囲内ならtrue
            if(1 <= nextNumber && nextNumber <= _pickupAreaInformations.Length) return true;

            return false;
        }

        void UpdateCurrentPickupIndex(int index)
        {
            // 副作用: _currentPickupIndexへの代入はこのメソッドのみで行う
            _currentPickupIndex = index;
        }
    }
}
