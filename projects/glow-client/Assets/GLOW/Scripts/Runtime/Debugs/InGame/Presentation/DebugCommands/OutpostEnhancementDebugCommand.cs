using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Debugs.Command.Presentations.Presenters;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Domain.Extensions;

namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    /// <summary>
    /// ゲート強化値変更デバッグコマンド
    /// </summary>
    public class OutpostEnhancementDebugCommand
    {
        public static void AddOutpostEnhancementButton(
            IDebugCommandPresenter presenter,
            OutpostEnhancementModel enhancementModel,
            Action<IReadOnlyDictionary<OutpostEnhancementType, OutpostEnhanceLevel>> onApplyButtonTapped)
        {
            presenter.AddNestedMenuButton(
                "ゲート強化Lv変更",
                nestedPresenter => CreateOutpostEnhancementChangeMenu(
                    nestedPresenter, 
                    enhancementModel, 
                    onApplyButtonTapped));
        }

        static void CreateOutpostEnhancementChangeMenu(
            IDebugCommandPresenter debugCommandPresenter,
            OutpostEnhancementModel enhancementModel,
            Action<IReadOnlyDictionary<OutpostEnhancementType, OutpostEnhanceLevel>> onApplyButtonTapped)
        {
            var enhancementTypes = new List<OutpostEnhancementType>();
            var currentLevels = new Dictionary<OutpostEnhancementType, string>();

            foreach (var element in enhancementModel.EnhancementElements)
            {
                enhancementTypes.Add(element.Type);
                currentLevels[element.Type] = element.Level.Value.ToString();
            }

            foreach (var type in enhancementTypes)
            {
                var label = ZString.Format("{0} Lv", type.ToDisplayString());
                
                debugCommandPresenter.AddTextBox(
                    label,
                    currentLevels[type],
                    value => currentLevels[type] = value
                );
            }

            debugCommandPresenter.AddButton("適用", () =>
            {
                var applyDictionary = new Dictionary<OutpostEnhancementType, OutpostEnhanceLevel>();
                foreach (var kvp in currentLevels)
                {
                    if (int.TryParse(kvp.Value, out var v))
                    {
                        applyDictionary[kvp.Key] = new OutpostEnhanceLevel(v);
                    }
                }
                onApplyButtonTapped?.Invoke(applyDictionary);
            });
        }
    }
}