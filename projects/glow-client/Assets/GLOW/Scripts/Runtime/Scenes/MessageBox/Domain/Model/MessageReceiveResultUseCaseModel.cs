using System.Collections.Generic;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.MessageBox.Domain.Model
{
    public record MessageReceiveResultUseCaseModel(
        IReadOnlyList<CommonReceiveResourceModel> ReceivedRewards,
        MessageCommonResultUseCaseModel CommonResultUseCaseModel);
}
