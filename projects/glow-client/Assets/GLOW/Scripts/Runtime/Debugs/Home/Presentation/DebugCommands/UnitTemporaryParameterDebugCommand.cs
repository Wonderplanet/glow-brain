#if GLOW_DEBUG
using System;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.Home.Domain.Models;
using GLOW.Debugs.Home.Domain.UseCases;
using GLOW.Debugs.InGame.Domain.Definitions;
using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Debugs.Home.Presentation.DebugCommands
{
    public class UnitTemporaryParameterDebugCommand
    {
        [Inject] DebugSetMstUnitTemporaryParameterUseCase DebugSetMstUnitTemporaryParameterUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] DebugUpdateSummonTemporaryParameterUseCase DebugSummonTemporaryParameterUpdateSummonUseCase { get; }
        [Inject] DebugGetMstUnitTemporaryParameterUseCase DebugGetMstUnitTemporaryParameterUseCase { get; }
        [Inject] DebugGetUnitTemporaryParameterUseCase DebugGetUnitTemporaryParameterUseCase { get; }
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }

        public void CreateTemporaryParameterMenu(IDebugCommandPresenter debugCommandPresenter, Action selectInGameAction)
        {
            var unitModels = DebugGetMstUnitTemporaryParameterUseCase.GetDebugDummyTemplates().ToList();
            var templateModels = TranslateToUnitTemporaryParameterModel(unitModels).ToList();

            for (int i = 0; i < templateModels.Count; i++)
            {
                int index = i;
                debugCommandPresenter.AddNestedMenuButton(
                    templateModels[index].Name.Value,
                    debugCommandPresenter => CreateUnitTemporaryParameterMenu(debugCommandPresenter, templateModels[index]));
            }

            var summonModels = DebugGetUnitTemporaryParameterUseCase.GetDummySummons().ToList();
            var templateSummonModels = TranslateToSummonTemporaryParameterModel(summonModels).ToList();

            for (int i = 0; i < summonModels.Count; i++)
            {
                int index = i;
                debugCommandPresenter.AddNestedMenuButton(
                    summonModels[index].Name.Value,
                    debugCommandPresenter => CreateSummonTemporaryParameterMenu(debugCommandPresenter, templateSummonModels[index]));
            }

            debugCommandPresenter.AddButton("【 インゲームで確認 】", () =>
            {
                selectInGameAction?.Invoke();
            });
        }

        void CreateUnitTemporaryParameterMenu(
            IDebugCommandPresenter debugCommandPresenter,
            DebugMstUnitTemporaryParameterModel model,
            bool isMultipleAttack = false,
            bool isMultipleSpecial = false)
        {
            string assetKey = model.AssetKey.Value;
            string moveSpeed = model.UnitMoveSpeed.Value.ToString();
            string attackRange = model.AttackRange.Value.ToString();

            var overrideUnit = OverrideAttackElement.Empty;

            string attackDelay = overrideUnit.NormalAttackDelay.ToString();
            string actionDuration = overrideUnit.NormalAttackDuration.ToString();
            string specialDelay = overrideUnit.SpecialAttackDelay.ToString();
            string specialDuration = overrideUnit.SpecialAttackDuration.ToString();

            List<AttackElement> dummyAttackElements = model.NormalAttackElements;
            List<AttackElement> dummySpecialElements = model.SpecialAttackElements;
            AttackElement dummyAttackElement = DebugMstUnitTemporaryParameterDefinitions.TemplateAttackElement;

            List<AttackFrameElement> defaultAttackElements = overrideUnit.NormalAttackElements
                .Select(element => new AttackFrameElement(element.Item1, element.Item2.ToString()))
                .ToList();;
            List<AttackFrameElement> defaultSpecialElements = overrideUnit.SpecialAttackElements
                .Select(element => new AttackFrameElement(element.Item1, element.Item2.ToString()))
                .ToList();;

            long attackTotalFrame = 0;
            long specialTotalFrame = 0;
            int hitStopCount = 0;
            long hitStopValue = 9;
            float hitStopSeconds = 0.17f;

            debugCommandPresenter.AddTextBox("アセットキー", assetKey, v => assetKey = v);
            debugCommandPresenter.AddStateButton("移動速度", new[] { "おそい", "ふつう", "はやい" }, "ふつう", v =>
            {
                moveSpeed = v switch
                {
                    "おそい" => "25.0",
                    "ふつう" => "35.0",
                    "はやい" => "45.0",
                    _ => moveSpeed
                };
            });
            debugCommandPresenter.AddStateButton("射程距離", new[] { "ちかい", "ふつう", "とおい" }, "ふつう", v =>
            {
                attackRange = v switch
                {
                    "ちかい" => "0.2",
                    "ふつう" => "0.4",
                    "とおい" => "0.6",
                    _ => attackRange
                };
            });
            string attackType = isMultipleAttack ? "多段攻撃" : "単発攻撃";
            debugCommandPresenter.AddButton(ZString.Format("攻撃タイプ:{0}",attackType), () =>
            {
                debugCommandPresenter.UpdateMenu(debugCommandPresenter, presenter =>
                {
                    CreateUnitTemporaryParameterMenu(debugCommandPresenter, model,  !isMultipleAttack, isMultipleSpecial);
                });
            });
            debugCommandPresenter.AddTextBox("通常攻撃の全体フレーム", overrideUnit.NormalAttackDuration.ToString(), v =>
            {
                actionDuration = SecondsToFrame(float.Parse(v)).ToString();
                overrideUnit = overrideUnit with
                {
                    NormalAttackDuration = float.Parse(v)
                };
            });
            if (isMultipleAttack)
            {
                List<(bool, string)> overrideElement = overrideUnit.NormalAttackElements
                    .Select(element => (element.Item1, element.Item2.ToString()))
                    .ToList();
                debugCommandPresenter.AddDropdownInputToggle("通常攻撃のフレーム", overrideElement, list =>
                {
                    var (updatedOverrideUnit, updatedAttackTotalFrame) = UpdateAttackElements(
                        list,
                        actionDuration,
                        hitStopValue,
                        hitStopSeconds,
                        dummyAttackElement,
                        dummyAttackElements,
                        overrideUnit
                    );

                    overrideUnit = updatedOverrideUnit;
                    attackTotalFrame = updatedAttackTotalFrame;
                });
            }
            else
            {
                bool isFirstHitStop = false;
                if (!defaultAttackElements.IsEmpty()) isFirstHitStop = defaultAttackElements[0].IsHitStop;
                debugCommandPresenter.AddInputToggle("通常攻撃のフレーム", overrideUnit.NormalAttackDelay.ToString(),
                    isFirstHitStop, isOn =>
                    {
                        dummyAttackElements.Clear();
                        var element = dummyAttackElement with
                        {
                            IsHitStop = new AttackHitStopFlag(isOn)
                        };

                        dummyAttackElements.Add(element);

                        if (isOn)
                        {
                            long.TryParse(actionDuration, out var parsedActionDuration);
                            actionDuration = (parsedActionDuration + hitStopValue).ToString();
                            overrideUnit = overrideUnit with
                            {
                                NormalAttackDuration = overrideUnit.NormalAttackDuration + hitStopSeconds
                            };
                        }
                        else
                        {
                            overrideUnit = overrideUnit with
                            {
                                NormalAttackDuration = overrideUnit.NormalAttackDuration - hitStopSeconds
                            };
                        }

                    }, v =>
                    {
                        float.TryParse(v, out var parsedDelay);
                        overrideUnit = overrideUnit with
                        {
                            NormalAttackDelay = parsedDelay
                        };
                        attackDelay = SecondsToFrame(parsedDelay).ToString();
                    });
            }

            debugCommandPresenter.AddButton(
                "通常攻撃のフレーム(HSあり)を表示する",
                () =>
                {
                    string tilte = "通常攻撃のフレーム(HSあり)";
                    string message = "";
                    if (isMultipleAttack)
                    {
                        for(int i = 0; i < overrideUnit.NormalAttackElements.Count; i++)
                        {
                            message += ZString.Format("発生タイミング{0} : {1}\\n", i + 1,
                                overrideUnit.NormalAttackElements[i].Item3);
                        }
                        message += "攻撃の全体フレーム : " + overrideUnit.NormalTotalDuration;
                    }
                    else
                    {
                        message = ZString.Format("発生タイミング : {0}\\n", overrideUnit.NormalAttackDelay);
                        message += "攻撃の全体フレーム : " + overrideUnit.NormalAttackDuration;
                    }

                    MessageViewUtil.ShowMessageWithButton(
                        tilte,
                        message,
                        string.Empty,
                        "閉じる",
                        () => { });
                });

            string specialType = isMultipleSpecial ? "多段攻撃" : "単発攻撃";
                debugCommandPresenter.AddButton(ZString.Format("必殺技タイプ:{0}", specialType),
                    () =>
                    {
                        debugCommandPresenter.UpdateMenu(debugCommandPresenter,
                            presenter =>
                            {
                                CreateUnitTemporaryParameterMenu(debugCommandPresenter, model, isMultipleAttack,
                                    !isMultipleSpecial);
                            });
                    });
                debugCommandPresenter.AddTextBox("必殺技の全体フレーム", overrideUnit.SpecialAttackDuration.ToString(), v =>
                {
                    overrideUnit = overrideUnit with
                    {
                        SpecialAttackDuration = float.Parse(v)
                    };
                    specialDuration = SecondsToFrame(float.Parse(v)).ToString();
                });
                if (isMultipleSpecial)
                {
                    // defaultSpecialElementsをboolとstringのタプルリストに変換
                    List<(bool, string)> overrideElement = overrideUnit.SpecialAttackElements
                        .Select(element => (element.Item1, element.Item2.ToString()))
                        .ToList();
                    debugCommandPresenter.AddDropdownInputToggle("必殺技のフレーム", overrideElement, list =>
                    {
                        var (updatedOverrideUnit, updatedSpecialTotalFrame) = UpdateSpecialAttackElements(
                            list,
                            specialDuration,
                            hitStopValue,
                            hitStopSeconds,
                            dummyAttackElement,
                            dummySpecialElements,
                            overrideUnit
                        );

                        overrideUnit = updatedOverrideUnit;
                        specialTotalFrame = updatedSpecialTotalFrame;
                    });
                }
                else
                {
                    bool isFirstHitStop = false;
                    if (!defaultSpecialElements.IsEmpty()) isFirstHitStop = defaultSpecialElements[0].IsHitStop;
                    debugCommandPresenter.AddInputToggle("必殺技のフレーム", overrideUnit.SpecialAttackDelay.ToString(), isFirstHitStop,
                        isOn =>
                        {
                            dummySpecialElements.Clear();
                            var element = dummyAttackElement with
                            {
                                IsHitStop = new AttackHitStopFlag(isOn)
                            };

                            dummySpecialElements.Add(element);

                            if (isOn)
                            {
                                long.TryParse(specialDuration, out var parsedSpecialDuration);
                                specialDuration = (parsedSpecialDuration + hitStopValue).ToString();
                                overrideUnit = overrideUnit with
                                {
                                    SpecialAttackDuration = overrideUnit.SpecialAttackDuration + hitStopSeconds
                                };
                            }
                            else
                            {
                                overrideUnit = overrideUnit with
                                {
                                    SpecialAttackDuration = overrideUnit.SpecialAttackDuration - hitStopSeconds
                                };
                            }

                        }, v =>
                        {
                            float.TryParse(v, out var parsedDelay);
                            overrideUnit = overrideUnit with
                            {
                                SpecialAttackDelay = parsedDelay
                            };
                            specialDelay = SecondsToFrame(parsedDelay).ToString();
                        });
                }

                debugCommandPresenter.AddButton(
                    "必殺技のフレーム(HSあり)を表示する",
                    () =>
                    {
                        string tilte = "必殺技のフレーム(HSあり)";
                        string message = "";
                        if (isMultipleSpecial)
                        {
                            for (int i = 0; i < overrideUnit.SpecialAttackElements.Count; i++)
                            {
                                message += ZString.Format("発生タイミング{0} : {1}\\n", i + 1,
                                    overrideUnit.SpecialAttackElements[i].Item3);
                            }

                            message += "攻撃の全体フレーム : " + overrideUnit.SpecialTotalDuration + "\\n";
                        }
                        else
                        {
                            message = ZString.Format("発生タイミング : {0}\\n", overrideUnit.SpecialAttackDelay);
                            message += "攻撃の全体フレーム : " + overrideUnit.SpecialAttackDuration + "\\n";
                        }

                        MessageViewUtil.ShowMessageWithButton(
                            tilte,
                            message,
                            string.Empty,
                            "閉じる",
                            () => { });
                    });

            debugCommandPresenter.AddButton("【 適用 】", () =>
            {
                float.TryParse(moveSpeed, out var parsedMoveSpeed);
                float.TryParse(attackRange, out var parsedWellDistance);
                long.TryParse(attackDelay, out var parsedAttackDelay);
                long.TryParse(isMultipleAttack ? attackTotalFrame.ToString() : actionDuration, out var parsedActionDuration);
                long.TryParse(specialDelay, out var parsedSpecialDelay);
                long.TryParse(specialDuration, out var parsedSpecialActionDuration);

                model = new DebugMstUnitTemporaryParameterModel(
                    model.Id,
                    model.Name,
                    new UnitAssetKey(assetKey),
                    new UnitMoveSpeed(parsedMoveSpeed),
                    new AttackRangeParameter(parsedWellDistance),
                    new TickCount(parsedAttackDelay),
                    new TickCount(parsedActionDuration),
                    new List<AttackElement>(dummyAttackElements),
                    new TickCount(parsedSpecialDelay),
                    new TickCount(parsedSpecialActionDuration),
                    new List<AttackElement>(dummySpecialElements)
                );

                var debugSettingModel = DebugSettingRepository.Get() with
                {
                    IsOverrideUnits = true
                };
                DebugSettingRepository.Save(debugSettingModel);
                DebugSetMstUnitTemporaryParameterUseCase.SetDebugUnitTemporaries(model);
            });
        }

        void CreateSummonTemporaryParameterMenu(
            IDebugCommandPresenter debugCommandPresenter,
            DebugSummonTemporaryParameterModel model,
            bool isMultipleAttack = false,
            bool isMultipleSpecial = false,
            bool isUseSpecialAttack = false)
        {
            string assetKey = model.AssetKey.Value;
            string moveSpeed = model.MoveSpeed.Value.ToString();
            string attackRange = model.AttackRange.Value.ToString();

            var overrideUnit = OverrideAttackElement.Empty;

            string attackDelay = overrideUnit.NormalAttackDelay.ToString();
            string actionDuration = overrideUnit.NormalAttackDuration.ToString();
            string specialDelay = overrideUnit.SpecialAttackDelay.ToString();
            string specialDuration = overrideUnit.SpecialAttackDuration.ToString();

            List<AttackElement> dummyAttackElements = model.NormalAttackElements;
            List<AttackElement> dummySpecialElements = model.SpecialAttackElements;
            AttackElement dummyAttackElement = DebugMstUnitTemporaryParameterDefinitions.TemplateAttackElement;

            List<AttackFrameElement> defaultAttackElements = overrideUnit.NormalAttackElements
                .Select(element => new AttackFrameElement(element.Item1, element.Item2.ToString()))
                .ToList();
            List<AttackFrameElement> defaultSpecialElements = overrideUnit.SpecialAttackElements
                .Select(element => new AttackFrameElement(element.Item1, element.Item2.ToString()))
                .ToList();

            int hitStopCount = 0;
            long attackTotalFrame = 0;
            long specialTotalFrame = 0;
            long hitStopValue = 9;
            float hitStopSeconds = 0.17f;

            debugCommandPresenter.AddTextBox("アセットキー", assetKey, v => assetKey = v);
            debugCommandPresenter.AddStateButton("移動速度", new[] { "おそい", "ふつう", "はやい" }, "ふつう", v =>
            {
                moveSpeed = v switch
                {
                    "おそい" => "25.0",
                    "ふつう" => "35.0",
                    "はやい" => "45.0",
                    _ => moveSpeed
                };
            });
            debugCommandPresenter.AddStateButton("射程距離", new[] { "ちかい", "ふつう", "とおい" }, "ふつう",v =>
            {
                attackRange = v switch
                {
                    "ちかい" => "0.3",
                    "ふつう" => "0.6",
                    "とおい" => "0.9",
                    _ => attackRange
                };
            });
            debugCommandPresenter.AddTextBox("通常攻撃の全体フレーム", overrideUnit.NormalAttackDuration.ToString(), v =>
            {
                actionDuration = SecondsToFrame(float.Parse(v)).ToString();
                overrideUnit = overrideUnit with
                {
                    NormalAttackDuration = float.Parse(v)
                };
            });
            string attackType = isMultipleAttack ? "多段攻撃" : "単発攻撃";
            debugCommandPresenter.AddButton(ZString.Format("攻撃タイプ:{0}", attackType), () =>
            {
                debugCommandPresenter.UpdateMenu(debugCommandPresenter, presenter =>
                {
                    CreateSummonTemporaryParameterMenu(debugCommandPresenter, model, !isMultipleAttack, isMultipleSpecial, isUseSpecialAttack);
                });
            });
            if (isMultipleAttack)
            {
                List<(bool, string)> overrideElement = overrideUnit.NormalAttackElements
                    .Select(element => ((bool)element.Item1, element.Item2.ToString()))
                    .ToList();
                debugCommandPresenter.AddDropdownInputToggle("通常攻撃のフレーム", overrideElement, list =>
                {
                    var (updatedOverrideUnit, updatedAttackTotalFrame) = UpdateAttackElements(
                        list,
                        actionDuration,
                        hitStopValue,
                        hitStopSeconds,
                        dummyAttackElement,
                        dummyAttackElements,
                        overrideUnit
                    );

                    overrideUnit = updatedOverrideUnit;
                    attackTotalFrame = updatedAttackTotalFrame;
                });
            }
            else
            {
                bool isFirstHitStop = false;
                if (!defaultAttackElements.IsEmpty()) isFirstHitStop = defaultAttackElements[0].IsHitStop;
                debugCommandPresenter.AddInputToggle("通常攻撃のフレーム", overrideUnit.NormalAttackDelay.ToString(), isFirstHitStop, isOn =>
                {
                    dummyAttackElements.Clear();
                    var element = dummyAttackElement with
                    {
                        IsHitStop = new AttackHitStopFlag(isOn)
                    };

                    dummyAttackElements.Add(element);

                    if (isOn)
                    {
                        long.TryParse(actionDuration, out var parsedActionDuration);
                        actionDuration = (parsedActionDuration + hitStopValue).ToString();
                        overrideUnit = overrideUnit with
                        {
                            NormalAttackDuration = overrideUnit.NormalAttackDuration + hitStopSeconds
                        };
                    }
                    else
                    {
                        overrideUnit = overrideUnit with
                        {
                            NormalAttackDuration = overrideUnit.NormalAttackDuration - hitStopSeconds
                        };
                    }

                }, v =>
                {
                    float.TryParse(v, out var parsedDelay);
                    overrideUnit = overrideUnit with
                    {
                        NormalAttackDelay = parsedDelay
                    };
                    attackDelay = SecondsToFrame(parsedDelay).ToString();
                } );
            }
            debugCommandPresenter.AddButton(
                "通常攻撃のフレーム(HSあり)を表示する",
                () =>
                {
                    string tilte = "通常攻撃のフレーム(HSあり)";
                    string message = "";
                    if (isMultipleAttack)
                    {
                        for(int i = 0; i < overrideUnit.NormalAttackElements.Count; i++)
                        {
                            message += ZString.Format("発生タイミング{0} : {1}\\n", i + 1,
                                overrideUnit.NormalAttackElements[i].Item3);
                        }
                        message += "攻撃の全体フレーム : " + overrideUnit.NormalTotalDuration;
                    }
                    else
                    {
                        message = ZString.Format("発生タイミング : {0}\\n", overrideUnit.NormalAttackDelay);
                        message += "攻撃の全体フレーム : " + overrideUnit.NormalAttackDuration + "\\n";
                    }

                    MessageViewUtil.ShowMessageWithButton(
                        tilte,
                        message,
                        string.Empty,
                        "閉じる",
                        () => { });
                });
            debugCommandPresenter.AddToggleButton("必殺技を使用するかどうか", isUseSpecialAttack, v =>
            {
                debugCommandPresenter.UpdateMenu(debugCommandPresenter, presenter =>
                {
                    CreateSummonTemporaryParameterMenu(debugCommandPresenter, model, isMultipleAttack, isMultipleSpecial, v);
                });
            });
            if (isUseSpecialAttack)
            {
                string specialType = isMultipleSpecial ? "多段攻撃" : "単発攻撃";
                debugCommandPresenter.AddButton(ZString.Format("必殺技タイプ:{0}", specialType),
                    () =>
                    {
                        debugCommandPresenter.UpdateMenu(debugCommandPresenter,
                            presenter =>
                            {
                                CreateSummonTemporaryParameterMenu(debugCommandPresenter, model,
                                    isMultipleAttack, !isMultipleSpecial, isUseSpecialAttack);
                            });
                    });
                debugCommandPresenter.AddTextBox("必殺技の全体フレーム", specialDuration, v =>
                {
                    float.TryParse(v, out var parsedDuration);
                    specialDuration = SecondsToFrame(parsedDuration).ToString();
                    overrideUnit = overrideUnit with
                    {
                        SpecialAttackDuration = parsedDuration
                    };
                });
                if (isMultipleSpecial)
                {
                    List<(bool, string)> overrideElement = overrideUnit.SpecialAttackElements
                        .Select(element => ((bool)element.Item1, element.Item2.ToString()))
                        .ToList();
                    debugCommandPresenter.AddDropdownInputToggle("必殺技のフレーム", overrideElement, list =>
                    {
                        var (updatedOverrideUnit, updatedSpecialTotalFrame) = UpdateSpecialAttackElements(
                            list,
                            specialDuration,
                            hitStopValue,
                            hitStopSeconds,
                            dummyAttackElement,
                            dummySpecialElements,
                            overrideUnit
                        );

                        overrideUnit = updatedOverrideUnit;
                        specialTotalFrame = updatedSpecialTotalFrame;
                    });
                }
                else
                {
                    bool isFirstHitStop = false;
                    if (!defaultSpecialElements.IsEmpty()) isFirstHitStop = defaultSpecialElements[0].IsHitStop;
                    debugCommandPresenter.AddInputToggle("必殺技のフレーム", overrideUnit.SpecialAttackDelay.ToString(), isFirstHitStop,
                        isOn =>
                        {
                            dummySpecialElements.Clear();
                            var element = dummyAttackElement with
                            {
                                IsHitStop = new AttackHitStopFlag(isOn)
                            };

                            dummySpecialElements.Add(element);

                            if (isOn)
                            {
                                long.TryParse(specialDuration, out var parsedSpecialDuration);
                                specialDuration = (parsedSpecialDuration + hitStopValue).ToString();
                                overrideUnit = overrideUnit with
                                {
                                    SpecialAttackDuration = overrideUnit.SpecialAttackDuration + hitStopSeconds
                                };
                            }
                            else
                            {
                                overrideUnit = overrideUnit with
                                {
                                    SpecialAttackDuration = overrideUnit.SpecialAttackDuration - hitStopSeconds
                                };
                            }

                        }, v =>
                        {
                            float.TryParse(v, out var parsedDelay);
                            overrideUnit = overrideUnit with
                            {
                                SpecialAttackDelay = parsedDelay
                            };
                            specialDelay = SecondsToFrame(parsedDelay).ToString();
                        });
                }

                debugCommandPresenter.AddButton(
                    "必殺技のフレーム(HSあり)を表示する",
                    () =>
                    {
                        string tilte = "必殺技のフレーム(HSあり)";
                        string message = "";
                        if (isMultipleSpecial)
                        {
                            for (int i = 0; i < overrideUnit.SpecialAttackElements.Count; i++)
                            {
                                message += ZString.Format("発生タイミング{0} : {1}\\n", i + 1,
                                    overrideUnit.SpecialAttackElements[i].Item3);
                            }

                            message += "攻撃の全体フレーム : " + overrideUnit.SpecialTotalDuration + "\\n";
                        }
                        else
                        {
                            message = ZString.Format("発生タイミング : {0}\\n", overrideUnit.SpecialAttackDelay);
                            message += "攻撃の全体フレーム : " + overrideUnit.SpecialAttackDuration + "\\n";
                        }

                        MessageViewUtil.ShowMessageWithButton(
                            tilte,
                            message,
                            string.Empty,
                            "閉じる",
                            () => { });
                    });
            }


            debugCommandPresenter.AddButton("【 適用 】", () =>
            {
                float.TryParse(moveSpeed, out var parsedMoveSpeed);
                float.TryParse(attackRange, out var parsedAttackRange);
                long.TryParse(attackDelay, out var parsedAttackDelay);
                long.TryParse(actionDuration, out var parsedActionDuration);
                long.TryParse(specialDelay, out var parsedSpecialDelay);
                long.TryParse(specialDuration, out var parsedSpecialActionDuration);

                model = new DebugSummonTemporaryParameterModel(
                    model.Id,
                    new UnitAssetKey(assetKey),
                    new UnitMoveSpeed(parsedMoveSpeed),
                    new AttackRangeParameter(parsedAttackRange),
                    new TickCount(parsedAttackDelay),
                    new TickCount(parsedActionDuration),
                    new List<AttackElement>(dummyAttackElements),
                    new TickCount(parsedSpecialDelay),
                    new TickCount(parsedSpecialActionDuration),
                    new List<AttackElement>(dummySpecialElements),
                    isUseSpecialAttack ? new AttackComboCycle(3) : AttackComboCycle.Empty
                );

                var debugSettingModel = DebugSettingRepository.Get() with
                {
                    IsOverrideSummons = true
                };
                DebugSettingRepository.Save(debugSettingModel);
                DebugSummonTemporaryParameterUpdateSummonUseCase.UpdateTemporaryParameters(model);
            });
        }

        long SecondsToFrame(float seconds)
        {
            return (long)Math.Ceiling(seconds * 50);
        }

        (OverrideAttackElement, long) UpdateAttackElements(
            List<(bool, string)> list,
            string actionDuration,
            long hitStopValue,
            float hitStopSeconds,
            AttackElement dummyAttackElement,
            List<AttackElement> dummyAttackElements,
            OverrideAttackElement overrideUnit)
        {
            int hitStopCount = 0;
            dummyAttackElements.Clear();
            var newNormalAttackElements = new List<(bool, float, float)>();

            foreach (var item in list)
            {
                long.TryParse(item.Item2, out var value);
                string hitValue;

                if (hitStopCount > 0)
                {
                    value += hitStopValue * hitStopCount;
                    float.TryParse(item.Item2, out var parsedValue);
                    hitValue = (parsedValue + hitStopSeconds * hitStopCount).ToString();
                }
                else
                {
                    hitValue = item.Item2;
                }

                if (item.Item1) hitStopCount++;

                var element = dummyAttackElement with
                {
                    IsHitStop = new AttackHitStopFlag(item.Item1),
                    AttackDelay = new TickCount(value)
                };
                dummyAttackElements.Add(element);

                float.TryParse(item.Item2, out var inputValue);
                float.TryParse(hitValue, out var inputHitValue);

                newNormalAttackElements.Add(new ValueTuple<bool, float, float>
                    (item.Item1, SecondsToFrame(inputValue), inputHitValue));
            }

            long.TryParse(actionDuration, out var parsedActionDuration);
            long attackTotalFrame = parsedActionDuration + hitStopValue * hitStopCount;

            var updatedOverrideUnit = overrideUnit with
            {
                NormalAttackElements = newNormalAttackElements,
                NormalTotalDuration = overrideUnit.NormalAttackDuration + hitStopSeconds * hitStopCount
            };

            return (updatedOverrideUnit, attackTotalFrame);
        }

        (OverrideAttackElement, long) UpdateSpecialAttackElements(
            List<(bool, string)> list,
            string specialDuration,
            long hitStopValue,
            float hitStopSeconds,
            AttackElement dummyAttackElement,
            List<AttackElement> dummySpecialElements,
            OverrideAttackElement overrideUnit)
        {
            int hitStopCount = 0;
            dummySpecialElements.Clear();
            var newSpecialAttackElements = new List<(bool, float, float)>();

            foreach (var item in list)
            {
                long.TryParse(item.Item2, out var value);
                string hitValue;

                if (hitStopCount > 0)
                {
                    value += hitStopValue * hitStopCount;
                    float.TryParse(item.Item2, out var parsedValue);
                    hitValue = (parsedValue + hitStopSeconds * hitStopCount).ToString();
                }
                else
                {
                    hitValue = item.Item2;
                }

                if (item.Item1) hitStopCount++;

                var element = dummyAttackElement with
                {
                    IsHitStop = new AttackHitStopFlag(item.Item1),
                    AttackDelay = new TickCount(value)
                };
                dummySpecialElements.Add(element);

                float.TryParse(item.Item2, out var inputValue);
                float.TryParse(hitValue, out var inputHitValue);

                newSpecialAttackElements.Add(new ValueTuple<bool, float, float>
                    (item.Item1, SecondsToFrame(inputValue), inputHitValue));
            }

            long.TryParse(specialDuration, out var parsedActionDuration);
            long specialTotalFrame = parsedActionDuration + hitStopValue * hitStopCount;

            var updatedOverrideUnit = overrideUnit with
            {
                SpecialAttackElements = newSpecialAttackElements,
                SpecialTotalDuration = overrideUnit.SpecialAttackDuration + hitStopSeconds * hitStopCount
            };

            return (updatedOverrideUnit, specialTotalFrame);
        }

        IReadOnlyList<DebugMstUnitTemporaryParameterModel> TranslateToUnitTemporaryParameterModel(List<MstCharacterModel> characterModels)
        {
            return characterModels
                .Select(m => new DebugMstUnitTemporaryParameterModel(
                    m.Id,
                    m.Name,
                    m.AssetKey,
                    m.UnitMoveSpeed,
                    m.NormalMstAttackModel.AttackData.AttackElements[0].AttackRange.EndPointParameter,
                    m.NormalMstAttackModel.AttackData.AttackDelay,
                    m.NormalMstAttackModel.AttackData.BaseData.ActionDuration,
                    m.NormalMstAttackModel.AttackData.AttackElements.ToList(),
                    m.SpecialAttacks[0].AttackData.AttackDelay,
                    m.SpecialAttacks[0].AttackData.BaseData.ActionDuration,
                    m.SpecialAttacks[0].AttackData.AttackElements.ToList()))
                .ToList();
        }

        IReadOnlyList<DebugSummonTemporaryParameterModel> TranslateToSummonTemporaryParameterModel(List<MstEnemyStageParameterModel> enemyModels)
        {
            return enemyModels
                .Select(m => new DebugSummonTemporaryParameterModel(
                    m.Id,
                    m.AssetKey,
                    m.UnitMoveSpeed,
                    m.NormalAttack.AttackElements[0].AttackRange.EndPointParameter,
                    m.NormalAttack.AttackDelay,
                    m.NormalAttack.BaseData.ActionDuration,
                    m.NormalAttack.AttackElements.ToList(),
                    m.SpecialAttack.AttackDelay,
                    m.SpecialAttack.BaseData.ActionDuration,
                    m.SpecialAttack.AttackElements.ToList(),
                    m.AttackComboCycle))
                .ToList();
        }
    }
}
#endif
