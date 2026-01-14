using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.MessageBox.Domain.Model;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;

namespace GLOW.Scenes.MessageBox.Presentation.Translator
{
    public class MessageBoxViewModelTranslator
    {
        public static MessageBoxViewModel ToMessageBoxViewModel(IReadOnlyList<MessageListUseCaseModel> models)
        {
            return new MessageBoxViewModel(
                models.Select(
                        model => new MessageBoxCellViewModel(
                            model.MessageId, 
                            model.MessageFormatType, 
                            model.MessageStatus, 
                            PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.MessageRewards), 
                            model.MessageTitle, 
                            model.MessageBody, 
                            model.MessageStartAtDate, 
                            model.LimitTime))
                    .ToList());
        }
    }
}
