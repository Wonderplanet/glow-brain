using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Transtator
{
    public class MessageBoxDetailViewModelTranslator
    {
        public static MessageBoxDetailViewModel ToMessageBoxDetailViewModel(IMessageBoxCellViewModel model)
        {
            return new MessageBoxDetailViewModel(
                model.MessageId,
                model.MessageFormatType,
                model.MessageStatus,
                model.MessageTitle,
                model.MessageBody,
                model.MessageRewards,
                model.MessageStartAtDate,
                model.LimitTime);
        }
    }
}