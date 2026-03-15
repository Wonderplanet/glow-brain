using System;
using System.Linq;
using GLOW.Debugs.Command.Domains.UseCase;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.DebugArtworkEffectDetail.Presentation
{
    public class DebugArtworkEffectDetailView : UIView
    {
        [SerializeField] Text _titleText;
        [SerializeField] Text _imformationText;

        public void Setup(DebugArtworkEffectElementUseCaseModel useCaseModel)
        {
            // タイトル
            _titleText.text = useCaseModel.GetNameString();

            _imformationText.text = "";
            // 基本情報
            _imformationText.text += $"====基本情報====\n";
            _imformationText.text += $"原画ID: {useCaseModel.Summary.MstArtworkId.Value}\n" +
                                     $"原画名: {useCaseModel.Summary.Name.Value}\n" +
                                     $"レアリティ: {useCaseModel.Summary.Rarity}\n" +
                                     $"カテゴリ: {useCaseModel.Summary.GetCategoryText()}\n" +
                                     $"ワザ概要: {DebugArtworkEffectTypeExtension.GetEffectTypeText(useCaseModel.Summary.EffectType)}\n" +
                                     $"効果対象数: {useCaseModel.Summary.TargetValue.Value}\n";

            // 発動条件
            var activationModel = useCaseModel.EffectActivation.EffectActivation;
            _imformationText.text += $"\n====発動条件====\n";
            _imformationText.text +=
                $"作品条件: {activationModel.GetSeriesActivation().Item1} / 作品条件対数: {activationModel.GetSeriesActivation().Item2}\n" +
                $"属性条件: {activationModel.GetColorActivation().Item1} / 属性条件対数: {activationModel.GetColorActivation().Item2}\n" +
                $"ロール条件: {activationModel.GetRoleActivation().Item1} / ロール条件対数: {activationModel.GetRoleActivation().Item2}\n" +
                $"キャラID: {activationModel.TargetMstUnitId.Value}\n";

            // 発動対象
            _imformationText.text += $"\n====発動対象====\n";
            _imformationText.text += $"発動対象作品: {useCaseModel.EffectTarget.GetSeriesTarget()}\n" +
                                     $"発動対象属性: {useCaseModel.EffectTarget.GetColorTarget()}\n" +
                                     $"発動対象ロール: {useCaseModel.EffectTarget.GetRoleTarget()}\n" +
                                     $"発動対象キャラ: {useCaseModel.EffectTarget.GetUnitTarget()}\n" +
                                     $"その他: {useCaseModel.EffectTarget.GetOtherTarget()}\n";

            // ワザ詳細
            _imformationText.text += $"\n====ワザ詳細====\n";
            _imformationText.text += $"ワザ内容: {useCaseModel.EffectDetail.GetEffectTypeString()}\n" +
                                     $"グレード1: {useCaseModel.EffectDetail.Grade1EffectValue.Value}\n" +
                                     $"グレード2: {useCaseModel.EffectDetail.Grade2EffectValue.Value}\n" +
                                     $"グレード3: {useCaseModel.EffectDetail.Grade3EffectValue.Value}\n" +
                                     $"グレード4: {useCaseModel.EffectDetail.Grade4EffectValue.Value}\n" +
                                     $"グレード5: {useCaseModel.EffectDetail.Grade5EffectValue.Value}\n";

            // 効果文言
            _imformationText.text += $"\n====効果文言====\n";
            for (int i = 0; i < useCaseModel.EffectDescriptions.Count; i++)
            {
                var description = useCaseModel.EffectDescriptions[i];
                _imformationText.text += $"グレード{description.GradeLevel.Value}:\n{description.Description.Value}\n";
                _imformationText.text += "\n";
            }

        }
    }
}
