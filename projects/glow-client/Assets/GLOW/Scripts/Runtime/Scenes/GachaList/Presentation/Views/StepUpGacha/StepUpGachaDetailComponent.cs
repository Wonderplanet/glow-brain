using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha;
using UnityEngine;
using WPFramework.Presentation.Modules;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.GachaList.Presentation.Views.StepUpGacha
{
    /// <summary>
    /// 71-1_ガシャ
    /// ステップアップガシャのコストやおまけ内容の1ステップ分の表示コンポーネント
    /// </summary> 
    public class StepUpGachaDetailComponent : UIObject
    {
        [Header("基本表示")]
        [SerializeField] UIObject _costRootObject;
        [SerializeField] UIText _drawCountText;
        [SerializeField] UIImage _drawCostImage;
        [SerializeField] UIObject _paidDiaCostObject;
        [SerializeField] UIText _costAmountText;
        [SerializeField] UIObject _costFreeObject;
        [SerializeField] UIObject _fixedPrizeRootObject;
        [SerializeField] UIText _fixedPrizeText;
        
        [Header("おまけ表示")]
        [SerializeField] UIObject _omakeObject;
        [SerializeField] UIImage _omakeImage;
        [SerializeField] UIText _omakeAmountText;


        public void Setup(StepUpGachaDetailViewModel viewModel)
        {
            // 基本情報設定
            SetupStepInfo(viewModel);
            
            // おまけ情報設定
            SetupOmakeInfo(viewModel);
        }

        void SetupStepInfo(StepUpGachaDetailViewModel viewModel)
        {
            // コスト表示
            _drawCountText.SetText("{0}回引く", viewModel.DrawCount.Value);
            
            // 無料の場合は[コスト画像]と[コスト消費数]を非表示にして[無料アイコン]を表示
            // 有償ダイヤの場合は[コスト画像]を非表示にして[有償ダイヤアイコン]を表示
            _drawCostImage.IsVisible = !viewModel.IsFree && viewModel.CostType != CostType.PaidDiamond;
            _paidDiaCostObject.IsVisible = !viewModel.IsFree && viewModel.CostType == CostType.PaidDiamond;
            _costAmountText.IsVisible = !viewModel.IsFree;
            _costFreeObject.IsVisible = viewModel.IsFree;
            
            if (!viewModel.IsFree)
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_drawCostImage.Image, viewModel.CostIconAssetPath.Value);
                _costAmountText.SetText("×{0}", AmountFormatter.FormatAmount((int)viewModel.CostAmount.Value));
            }
            
            // 確定枠説明文設定
            _fixedPrizeRootObject.IsVisible = !viewModel.FixedPrizeDescription.IsEmpty();
            _fixedPrizeText.SetText(viewModel.FixedPrizeDescription.Value);
        }

        void SetupOmakeInfo(StepUpGachaDetailViewModel viewModel)
        {
            // おまけがない場合は非表示にしてreturn
            _omakeObject.IsVisible = viewModel.HasOmake;
            if (!viewModel.HasOmake) return;
            
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_omakeImage.Image, viewModel.OmakeIconAssetPath.Value);
            
            // キャラの場合は個数表示を非表示にする
            _omakeAmountText.IsVisible = viewModel.OmakeResourceType != ResourceType.Unit;
            _omakeAmountText.SetText("×{0}", AmountFormatter.FormatAmount(viewModel.OmakeAmount.Value));
            
        }

    }
}