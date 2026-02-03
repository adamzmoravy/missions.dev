import WelcomePage from './pages/welcome-page.vue'
import GroupPage from './pages/group-page.vue'
import NotFoundPage from './pages/not-found-page.vue'
import IdeationPage from './pages/ideation-page.vue'
import MembersPage from './pages/members-page.vue'
import SubmissionPage from './pages/submission-page.vue'
import GuidePage from './pages/guide-page.vue'
import IssuesPage from './pages/issues-page.vue'
import PortfolioPage from './pages/portfolio-page.vue'
import FrontPage from './pages/front-page.vue'
import FeedPage from './pages/feed-page.vue'
import AdminPageSurveys from './pages/admin-page-surveys.vue'
import ProfilePage from './pages/profile-page.vue'
import OnboardingPage from './pages/onboarding-page.vue'
import ResourcePage from './pages/resource-page.vue'
import AdminPage from './pages/admin-page.vue'
import BookingsPage from './pages/bookings-page.vue'
import JoinPage from './pages/join-page.vue'
import MeshPage from './pages/mesh-page.vue'
import AccessPage from './pages/access-page.vue'
import WelcomeLandingPage from './pages/welcome-landing-page.vue'
import MonitorPage from './pages/monitor-page.vue'
import PasswordResetPage from './pages/password-reset-page.vue'
import OrganisationsPage from './pages/organisations-page.vue'
import EventsPage from './pages/events-page.vue'
import PlatformNewPage from './pages/platform-new-page.vue'
import submissionsSlideshowPage from './pages/submissions-slideshow-page.vue'
import qaPage from './pages/qa-page.vue'
import PoliciesPage from './pages/policies-page.vue'

import { useGroupsStore } from './stores/groups'
import { useUIStore } from './stores/ui'
import { useAuthStore } from './stores/auth'
import { usePlatformStore } from './stores/platform'


const pages = {
  'password-reset': PasswordResetPage,
  'welcome': WelcomePage,
  'group': GroupPage,
  'book': BookingsPage,
  'ideation': IdeationPage,
  'members': MembersPage,
  'submission': SubmissionPage,
  'guide': GuidePage,
  'issues': IssuesPage,
  'portfolio': PortfolioPage,
  'front': FrontPage.AdminPageSurveys,
  'profile': ProfilePage,
  'onboarding': OnboardingPage,
  'resource': ResourcePage,
  'admin': AdminPage,
  'join': JoinPage,
  'mesh': MeshPage,
  'access': AccessPage,
  'monitor': MonitorPage,
  'organisations': OrganisationsPage,
  'events': EventsPage,
  'platform-new-page': PlatformNewPage,
  'submissions-slideshow': submissionsSlideshowPage,
  'qa': qaPage,
  'policies': PoliciesPage
}


export default {

    routes: [
      {
        path: '/:lang/policies/:policy/',
        name: 'policies',
        component: PoliciesPage,
        meta: {
          byPassAuthentication: true
        }

      },
      { 
        path: '/:lang/platform-new/',
        name: 'platform-new',
        component: PlatformNewPage,
        meta: {
          byPassAuthentication: true,
          hideHeader: true          
        }        
      },
      {
        path: '/:lang/submissions-slideshow/',
        name: 'submissions-slideshow',
        component: submissionsSlideshowPage,
        meta: {
            hideHeader: true
        }
      },
      { 
        path: '/:lang/landing/',
        name: 'landing',
        component: WelcomeLandingPage,
        meta: {
        }        
      },
      {
        path: '/:lang/password-reset/',
        name: 'password-reset',
        component: PasswordResetPage,
        meta: {
          byPassAuthentication: true,
          changePassword: false
        }
      },
      {
        path: '/:lang/password-change/:token/',
        name: 'password-change',
        component: PasswordResetPage,
        meta: {
          byPassAuthentication: true,
          changePassword: true
        }
      },
      {
          path: '/:lang/talk/',
          name: 'talk',
          component: GroupPage,
          meta: {

          }
      },
      {
        path: '/:lang/events/',
        name: 'events',
        component: EventsPage,
        meta: {

        }
      },
      { 
        path: '/:lang/monitor/',
        name: 'monitor',
        component: pages['monitor'],
        meta: {
          byPassAuthentication: true,
          hideHeader: true
        }        
      },
      {
        path: '/:lang/access/:token/',
        name: 'access',
        component: AccessPage,
        meta: {
          byPassAuthentication: true
        }
      },
      { 
        path: '/:lang/mesh/:board/',
        alias: '/:lang/canvas/:board/',
        name: 'mesh',
        component: MeshPage
      },
      { 
        path: '/:lang/join/',
        name: 'join', 
        component: JoinPage
      },
      { 
        path: '/:lang/admin/:section/',
        name: 'admin',
        component: AdminPage
      },      
      { 
        path: '/:lang/profile/:profileId/',
        name: 'profile',
        component: ProfilePage
      },      
      { 
        path: '/:lang/resource/:resourceId/',
        name: 'resource',
        component: ResourcePage
      },
      { 
        path: '/:lang/onboarding/',
        name: 'onboarding',
        component: OnboardingPage,
        alias: '/:lang/register/',
        meta: {
          byPassAuthentication: true
        }
      },
      { 
        path: '/:pathMatch(.*)*', 
        name: 'NotFound', 
        component: NotFoundPage,
        beforeEnter: (to, from, next) => {
          // get first slug from path
          const lang = to.path.split('/')[1]

          const ui = useUIStore()
          if (lang.length != 2) {
            next( {
              path: ui.lang + to.path,
              replace: true // Use replace instead of push
            })
          } else {
            next()
          }
        }        
      },
      { 
        path: '/:lang([a-zA-Z]{2})/book/',
        name: 'book', 
        component: BookingsPage
      },
      { 
        path: '/:lang([a-zA-Z]{2})/',
        alias: '/:lang([a-zA-Z]{2})/front',
        name: 'fronter',
        component: FrontPage
      },
       
/*      { 
        path: '/:lang([a-zA-Z]{2})/',
        alias: '/:lang([a-zA-Z]{2})/front',
        name: 'fronter',
        component: FrontPage,
        beforeEnter: (to, from, next) => {
          const auth = useAuthStore()
          const platform = usePlatformStore()
          console.log(platform.ui)
          console.log(auth.isAuthenticated)

          if (platform.ui && platform.ui.frontPage) {
            next({
              path: '/' + to.params.lang + '/talk/',
              replace: true 
            })
            
          }
          else if (auth.isAuthenticated) {
            next({
              path: '/' + to.params.lang + '/feed/',
              replace: true 
            })
          } else {
            next({
              path: '/' + to.params.lang + '/welcome/',
              replace: true
            })
          }
        }
      },
*/
      { 
        path: '/:lang([a-zA-Z]{2})/admin/surveys/:surveyName/',
        name: 'admin-surveys',
        component: AdminPageSurveys 
      },
      { 
        path: '/:lang([a-zA-Z]{2})/feed/',
        alias: '/:lang([a-zA-Z]{2})/home/',
        name: 'feed',
        component: FeedPage 
      },
      { 
        path: '/:lang([a-zA-Z]{2})/issues/',
        name: 'issues',
        component: IssuesPage 
      },
      { 
        path: '/:lang([a-zA-Z]{2})/qa/',
        name: 'qa',
        component: qaPage 
      },
      { 
        path: '/:lang([a-zA-Z]{2})/portfolio/',
        name: 'portfolio',
        component: PortfolioPage
      },
      {
        path: '/:lang([a-zA-Z]{2})/guide',
        name: 'guide',
        component: GuidePage
      },  
      {
        path: '/:lang([a-zA-Z]{2})/organisations',
        name: 'organisations',
        component: OrganisationsPage
      },  
      { 
        path: '/:lang([a-zA-Z]{2})/submission/:id/',
        name: 'submission',
        component: SubmissionPage,
        alias: '/:lang([a-zA-Z]{2})/experiment/:id/',
      },
      {
        path: '/:lang([a-zA-Z]{2})/members/',
        alias: '/:lang([a-zA-Z]{2})/people/',
        name: 'members',
        component: MembersPage
      },  
      { 
        path: '/:lang([a-zA-Z]{2})/welcome/',
        name: 'front',
        component: pages['welcome']
      },
      { 
        path: '/:lang([a-zA-Z]{2})/ideation/',
        name: 'ideation',
        component: IdeationPage
      },
      { 
        path: '/:lang([a-zA-Z]{2})/submissions/',
        name: 'submissions',
        component: IdeationPage
      },
      { 
        path: '/:lang([a-zA-Z]{2})/group/:groupId/',
        name: 'group',
        component: GroupPage,
        alias: '/:lang([a-zA-Z]{2})/happening/:groupId/',
        beforeEnter: (to, from, next) => {
          if (from.name !== null) {

            const groups = useGroupsStore()

            if (groups.groups.length === 0) {
              next()
            }
            groups.previewIfRequired(to.params.groupId, next)  

          } else {
            next()
          }

        }  
      }
    ]
}
