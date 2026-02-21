using System.Collections.Generic;

namespace GLOW.Modules.Tutorial.Domain.Model
{
    public record TutorialSequenceUseCaseModel(IReadOnlyList<TutorialSequenceModel> TutorialSequenceModels);
}
