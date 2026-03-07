#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.InGame.Domain.Constants.DebugSkillTemplates;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    // デバッグコマンド：スキル操作
    public static class SkillOperationDebugCommand
    {
        public static void AddSkillOperationButton(
            IDebugCommandPresenter presenter,
            IReadOnlyList<DebugSkillOperationUnitInfoModel> unitInfos,
            Action<MasterDataId, BattleSide, IReadOnlyList<DebugAttackElementData>> onTemplateSelected)
        {
            presenter.AddNestedMenuButton("スキル操作", nestedPresenter =>
            {
                foreach (var unitInfo in unitInfos)
                {
                    AddUnitButton(nestedPresenter, unitInfo, onTemplateSelected);
                }
            });
        }

        static void AddUnitButton(
            IDebugCommandPresenter presenter,
            DebugSkillOperationUnitInfoModel unitInfo,
            Action<MasterDataId, BattleSide, IReadOnlyList<DebugAttackElementData>> onTemplateSelected)
        {
            var label = ZString.Format("{0}: {1}",
                unitInfo.BattleSide == BattleSide.Player ? "味方" : "敵",
                unitInfo.CharacterName);

            presenter.AddNestedMenuButton(label, nestedPresenter =>
            {
                AddSpecialAttackButton(nestedPresenter, unitInfo, onTemplateSelected);
            });
        }

        static void AddSpecialAttackButton(
            IDebugCommandPresenter presenter,
            DebugSkillOperationUnitInfoModel unitInfo,
            Action<MasterDataId, BattleSide, IReadOnlyList<DebugAttackElementData>> onTemplateSelected)
        {
            presenter.AddNestedMenuButton("必殺技変更", nestedPresenter =>
            {
                // 効果グループ一覧
                var categories = new List<(string name, Func<IReadOnlyList<(string, IReadOnlyList<DebugAttackElementData>)>> getTemplates)>
                {
                    ("バフ解除", () => DebugRemoveBuffTemplates.GetTemplates()),
                    ("デバフ解除", () => DebugRemoveDebuffTemplates.GetTemplates()),
                    ("その他", () => DebugOthersTemplates.GetTemplates()),
                };

                foreach (var (categoryName, getTemplates) in categories)
                {
                    nestedPresenter.AddNestedMenuButton(categoryName, categoryPresenter =>
                    {
                        var templates = getTemplates();

                        foreach (var (templateName, attackElementDataList) in templates)
                        {
                            categoryPresenter.AddButton(templateName, () =>
                            {
                                onTemplateSelected(
                                    unitInfo.CharacterId,
                                    unitInfo.BattleSide,
                                    attackElementDataList);
                            });
                        }
                    });
                }
            });
        }
    }
}
#endif

